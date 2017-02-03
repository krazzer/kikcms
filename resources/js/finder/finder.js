var Finder = function () {
};

Finder.prototype =
{
    instance: null,

    init: function () {
        this.initUpload();
        this.initFiles();
    },

    initFiles: function () {
        this.getFinder().find('.files .file').click(function () {
            var $file = $(this);

            $file.addClass('selected');
        });
    },

    initUpload: function () {
        var $uploadButton = this.getFinder().find('.button.upload');
        var $progressBar  = this.getFinder().find('.progress-bar');

        $uploadButton.find('input').on('click', function (e) {
            if ($uploadButton.hasClass('disabled')) {
                e.preventDefault();
            }
        });

        //todo: add devense and easyfi
        $uploadButton.find('input').on('change', function () {
            var formData = new FormData();
            // Loop through each of the selected files.
            for (var i = 0; i < this.files.length; i++) {
                var file = this.files[i];

                // Add the file to the request.
                formData.append('files[]', file);
            }

            $uploadButton.addClass('disabled');
            $progressBar.parent().fadeIn();

            KikCMS.action('/finder/upload', formData, function () {
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
        });
    },

    getFinder: function () {
        return $("#" + this.instance);
    }
};