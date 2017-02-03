var Finder = function () {
};

Finder.prototype =
{
    instance: null,
    shiftKeyPressed: false,

    action: function (action, parameters, onSuccess) {
        KikCMS.action('/finder/' + action, parameters, onSuccess);
    },

    fileDeSelect: function ($file) {
        $file.removeClass('selected');

        this.updateToolbar();
    },

    fileSelect: function ($file) {
        $file.addClass('selected');

        this.updateToolbar();
    },

    init: function () {
        this.initUpload();
        this.initFiles();
        this.initKeyEvents();
        this.initButtons();
    },

    initButtons: function () {
        var self = this;

        this.getToolbar().find('.delete').click(function () {
            var selectedIds = self.getSelectedFileIds();
            console.log(selectedIds);

            self.action('delete', {fileIds: selectedIds}, function (result) {
                self.setFilesContainer(result.files);
            })
        });
    },

    initFiles: function () {
        var self           = this;
        var $fileContainer = this.getFileContainer();
        var $files         = $fileContainer.find('.file');

        $files.each(function () {
            var $file            = $(this);
            var $fileSelectables = $file.find('img, .glyphicon, .extension, .name span');

            // dont de-select selected files on click
            $file.find('.thumb').click(function (e) {
                if ($(this).parent().hasClass('selected')) {
                    e.stopPropagation();

                    if (self.shiftKeyPressed) {
                        self.fileDeSelect($file);
                    }
                }
            });

            // don't drag images
            $file.find('img').on('dragstart', function (e) {
                e.preventDefault();
            });

            // select a file
            $fileSelectables.click(function (e) {
                if (!self.shiftKeyPressed) {
                    $fileContainer.find('.file.selected').removeClass('selected');
                }

                if ($file.hasClass('selected') && self.shiftKeyPressed) {
                    self.fileDeSelect($file);
                } else {
                    self.fileSelect($file);
                }

                e.stopPropagation();
            });

            $fileSelectables.on('dblclick', function (e) {
                window.open($file.attr('data-url'));
                e.stopPropagation();
            });
        });

        $files.on('dblclick', function () {
            var $file = $(this);

            if ($file.hasClass('selected')) {
                window.open($file.attr('data-url'));
            }
        });

        // de-select files if we click somewhere else
        this.getFileContainer().click(function () {
            if (self.shiftKeyPressed) {
                return;
            }

            $fileContainer.find('.file.selected').each(function () {
                self.fileDeSelect($(this));
            });
        });

        this.updateToolbar();
    },

    initKeyEvents: function () {
        var self = this;

        $(document).keydown(function (e) {
            if (e.keyCode == keyCode.SHIFT) {
                self.shiftKeyPressed = true;
            }
        });

        $(document).keyup(function (e) {
            if (e.keyCode == keyCode.SHIFT) {
                self.shiftKeyPressed = false;
            }
        });
    },

    initUpload: function () {
        var self          = this;
        var $uploadButton = this.getFinder().find('.button.upload');
        var $progressBar  = this.getFinder().find('.progress-bar');

        $uploadButton.find('input').on('click', function (e) {
            if ($uploadButton.hasClass('disabled')) {
                e.preventDefault();
            }
        });

        //todo: add defense and easyfi
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

            KikCMS.action('/finder/upload', formData, function (result) {
                self.setFilesContainer(result.files, result.uploadStatus);

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
    },

    getFileContainer: function () {
        return this.getFinder().find('.files .files-container');
    },

    setFilesContainer: function (html, uploadStatus) {
        var $filesContainer = this.getFileContainer();

        $filesContainer.html(html);

        this.initFiles();

        $.each(uploadStatus, function (index, fileId) {
            if (fileId === false) {
                return;
            }

            var $createdFile = $filesContainer.find('.file-' + fileId);
            $createdFile.addClass('created');

            setTimeout(function () {
                $createdFile.find('.thumb').addClass('easeOutBgColor');
                $createdFile.removeClass('created');

                setTimeout(function () {
                    $createdFile.find('.thumb').removeClass('easeOutBgColor');
                }, 500);
            }, 5000);
        })
    },

    getSelectedFileIds: function () {
        var ids = [];

        this.getFileContainer().find('.file.selected').each(function () {
            ids.push($(this).attr('data-id'));
        });

        return ids;
    },

    getToolbar: function () {
        return this.getFinder().find('.toolbar');
    },

    /**
     * Checks if any toolbar button should be updated by selected files etc
     */
    updateToolbar: function () {
        var aFileIsSelected = this.getFileContainer().find('.file.selected').length >= 1;
        var $toolbar        = this.getToolbar();

        if (aFileIsSelected) {
            $toolbar.find('.delete, .copy, .cut').removeClass('faded');
        } else {
            $toolbar.find('.delete, .copy, .cut').addClass('faded');
        }
    }
};