var FileUploader = function (options) {
    this.onSuccess     = options.onSuccess;
    this.$container    = options.$container;
    this.$uploadButton = options.$uploadButton ? options.$uploadButton : this.$container.find('.upload');
    this.action        = options.action ? options.action : '/cms/finder/upload';
    this.fileTypes     = options.fileTypes ? options.fileTypes : KikCMS.allowedExt;

    if (options.addParametersBeforeUpload) {
        this.addParametersBeforeUpload = options.addParametersBeforeUpload;
    } else {
        this.addParametersBeforeUpload = function (formData) {
            return formData;
        };
    }
};

FileUploader.prototype = {
    init: function () {
        var self = this;

        this.getInput().on('click', function (e) {
            if (self.$uploadButton.hasClass('disabled')) {
                e.preventDefault();
            }
        });

        this.getInput().on('change', function () {
            var formData   = new FormData();
            var fileAmount = this.files.length;
            var filesAdded = 0;

            if (fileAmount > KikCMS.maxFileUploads) {
                alert(KikCMS.tl('media.uploadMaxFilesWarning', {amount: KikCMS.maxFileUploads}));
                return self.cancel();
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

            if (!self.checkFileTypes(this.files) || !filesAdded) {
                return self.cancel();
            }

            self.actionUpload(formData);
        });
    },

    actionUpload: function (formData) {
        var self         = this;
        var $progressBar = this.$container.find('.progress-bar');

        this.$uploadButton.addClass('disabled');

        $progressBar.width(0);
        $progressBar.attr('aria-valuenow', 0);
        $progressBar.parent().fadeIn();

        formData = this.addParametersBeforeUpload(formData);

        KikCMS.action(this.action, formData, function (result) {
            self.onSuccess(result);

            if (result.errors && result.errors.length > 0) {
                alert(result.errors.join("\n"));
            }

            self.$uploadButton.removeClass('disabled');
            self.getInput().val('');
            $progressBar.parent().fadeOut();
        }, function () {
            self.$uploadButton.removeClass('disabled');
            self.getInput().val('');
            $progressBar.parent().fadeOut();
        }, function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) { // if upload property exists
                var lastPos = 0;

                myXhr.upload.addEventListener('progress', function (progress) {
                    var percentage = (progress.position / progress.totalSize) * 100;

                    if(percentage >= lastPos + 1 || percentage > 99) {
                        lastPos = percentage;

                        $progressBar.width(percentage + '%');
                        $progressBar.attr('aria-valuenow', percentage);
                    }
                }, false);
            }
            return myXhr;
        });
    },

    /**
     * @return {boolean}
     */
    cancel: function(){
        this.getInput().val('');
        return false;
    },

    /**
     * Check if given filename has a valid extension
     * @param fileName
     */
    checkFileType: function (fileName) {
        for (var k = 0; k < this.fileTypes.length; k++) {
            var ext = this.fileTypes[k];

            if (fileName.substr(fileName.length - ext.length, ext.length).toLowerCase() == ext.toLowerCase()) {
                return true;
            }
        }

        return false;
    },

    /**
     * Check if given files are having the valid extension
     *
     * @param files
     */
    checkFileTypes: function (files) {
        for (var j = 0; j < files.length; j++) {
            if( ! this.checkFileType(files[j].name)){
                alert(KikCMS.tl('media.fileTypeWarning') + this.fileTypes.join(', '));
                return false;
            }
        }

        return true;
    },

    /**
     * @return {*}
     */
    getInput: function () {
        return this.$uploadButton.find('input');
    }
};