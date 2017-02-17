var FinderFileUploader = function (options) {
    this.onSuccess  = options.onSuccess;
    this.$container = options.$container;
    this.action     = options.action ? options.action : '/finder/upload';

    if (options.addParametersBeforeUpload) {
        this.addParametersBeforeUpload = options.addParametersBeforeUpload;
    } else {
        this.addParametersBeforeUpload = function (formData) {
            return formData;
        };
    }
};

FinderFileUploader.prototype =
{
    init: function () {
        var self          = this;
        var $uploadButton = this.$container.find('.btn.upload');

        $uploadButton.find('input').on('click', function (e) {
            if ($uploadButton.hasClass('disabled')) {
                e.preventDefault();
            }
        });

        $uploadButton.find('input').on('change', function () {
            var $fileInput    = $(this);
            var formData      = new FormData();
            var fileAmount    = this.files.length;
            var maxFileAmount = $fileInput.attr('data-max-file-uploads');

            if (fileAmount > maxFileAmount) {
                alert(KikCMS.tl('media.uploadMaxFilesWarning', {amount: maxFileAmount}));
                return;
            }

            for (var i = 0; i < fileAmount; i++) {
                formData.append('files[]', this.files[i]);
            }

            self.actionUpload(formData);
        });
    },

    actionUpload: function (formData) {
        var self          = this;
        var $uploadButton = this.$container.find('.btn.upload');
        var $progressBar  = this.$container.find('.progress-bar');

        $uploadButton.addClass('disabled');
        $progressBar.parent().fadeIn();

        formData = this.addParametersBeforeUpload(formData);

        KikCMS.action(this.action, formData, function (result) {
            self.onSuccess(result);

            if (result.errors.length > 0) {
                alert(result.errors.join("\n"));
            }

            $uploadButton.removeClass('disabled');
            $progressBar.parent().fadeOut();
        }, function () {
            $uploadButton.removeClass('disabled');
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
    }
};