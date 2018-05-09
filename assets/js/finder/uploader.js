var FinderFileUploader = function (options) {
    this.onSuccess     = options.onSuccess;
    this.$container    = options.$container;
    this.$uploadButton = options.$uploadButton ? options.$uploadButton : this.$container.find('.btn.upload');
    this.action        = options.action ? options.action : '/cms/finder/upload';
    this.fileTypes     = options.fileTypes ? options.fileTypes : [];

    if (options.addParametersBeforeUpload) {
        this.addParametersBeforeUpload = options.addParametersBeforeUpload;
    } else {
        this.addParametersBeforeUpload = function (formData) {
            return formData;
        };
    }
};

FinderFileUploader.prototype = {
    init: function () {
        var self = this;

        this.$uploadButton.find('input').on('click', function (e) {
            if (self.$uploadButton.hasClass('disabled')) {
                e.preventDefault();
            }
        });

        this.$uploadButton.find('input').on('change', function () {
            var formData   = new FormData();
            var fileAmount = this.files.length;
            var filesAdded = 0;

            if (fileAmount > KikCMS.maxFileAmount) {
                alert(KikCMS.tl('media.uploadMaxFilesWarning', {amount: KikCMS.maxFileAmount}));
                return;
            }

            for (var i = 0; i < fileAmount; i++) {
                var file = this.files[i];

                if (file.size > KikCMS.maxFileSize) {
                    alert(KikCMS.tl('media.uploadMaxFileSizeWarning', {max: KikCMS.maxFileSizeString}));
                    continue;
                }

                formData.append('files[]', file);
                filesAdded++;
            }

            if (!self.checkFileTypes(this.files)) {
                return;
            }

            if (!filesAdded) {
                return;
            }

            self.actionUpload(formData);
        });
    },

    actionUpload: function (formData) {
        var self         = this;
        var $progressBar = this.$container.find('.progress-bar');

        this.$uploadButton.addClass('disabled');
        $progressBar.parent().fadeIn();

        formData = this.addParametersBeforeUpload(formData);

        KikCMS.action(this.action, formData, function (result) {
            self.onSuccess(result);

            if (result.errors && result.errors.length > 0) {
                alert(result.errors.join("\n"));
            }

            self.$uploadButton.removeClass('disabled');
            self.$uploadButton.find('input').val('');
            $progressBar.parent().fadeOut();
        }, function () {
            self.$uploadButton.removeClass('disabled');
            self.$uploadButton.find('input').val('');
            $progressBar.parent().fadeOut();
        }, function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) { // if upload property exists
                myXhr.upload.addEventListener('progress', function (progress) {
                    var percentage = (progress.position / progress.totalSize) * 100;
                    $progressBar.width(percentage + '%');
                    $progressBar.attr('aria-valuenow', percentage);
                }, false);
            }
            return myXhr;
        });
    },

    /**
     * Check if given files are having the valid extension
     *
     * @param files
     */
    checkFileTypes: function (files) {
        for (var j = 0; j < files.length; j++) {
            var fileName = files[j].name;

            for (var k = 0; k < this.fileTypes.length; k++) {
                var ext = this.fileTypes[j];

                if (fileName.substr(fileName.length - ext.length, ext.length).toLowerCase() != ext.toLowerCase()) {
                    alert(KikCMS.tl('media.fileTypeWarning') + this.fileTypes.join(', '));
                    return;
                }
            }
        }

        return true;
    }
};