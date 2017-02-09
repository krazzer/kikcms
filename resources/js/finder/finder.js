var Finder = function () {
};

Finder.prototype =
{
    instance: null,
    shiftKeyPressed: false,

    action: function (action, parameters, onSuccess) {
        if (typeof parameters.folderId == 'undefined') {
            parameters.folderId = this.getCurrentFolderId();
        }

        KikCMS.action('/finder/' + action, parameters, onSuccess);
    },

    actionAddFolder: function () {
        var self       = this;
        var folderName = prompt(KikCMS.tl('media.createFolder'), KikCMS.tl('media.defaultFolderName'));

        if (!folderName) {
            return;
        }

        this.action('createFolder', {folderName: folderName}, function (result) {
            self.setFilesContainer(result.files);
        })
    },

    actionDelete: function () {
        var self        = this;
        var selectedIds = this.getSelectedFileIds();

        var confirmMessage = selectedIds.length > 1
            ? KikCMS.tl('media.deleteConfirm', {amount: selectedIds.length})
            : KikCMS.tl('media.deleteConfirmOne');

        if (!confirm(confirmMessage)) {
            return;
        }

        this.action('delete', {fileIds: selectedIds}, function (result) {
            self.setFilesContainer(result.files);
        })
    },

    actionOpenFolder: function (folderId) {
        var self = this;

        this.saveCurrentFolderId(folderId);

        this.action('openFolder', {folderId: folderId}, function (result) {
            self.setFilesContainer(result.files);
            self.setPath(result.path);
        })
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
        this.initSearch();
        this.initPath();
    },

    initButtons: function () {
        this.getToolbar().find('.delete').click(this.actionDelete.bind(this));
        this.getToolbar().find('.addFolder').click(this.actionAddFolder.bind(this));
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
                if ($file.hasClass('folder')) {
                    self.actionOpenFolder($file.attr('data-id'));
                    e.stopPropagation();
                    return;
                }

                window.open($file.attr('data-url'));
                e.stopPropagation();
            });
        });

        $files.on('dblclick', function () {
            var $file = $(this);

            if ($file.hasClass('folder')) {
                self.actionOpenFolder($file.attr('data-id'));
                return;
            }

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

    initPath: function () {
        var self = this;

        this.getFinder().find('.path ul li a').click(function () {
            var folderId = $(this).attr('data-id');
            self.actionOpenFolder(folderId);
        });
    },

    initSearch: function () {
        var self = this;

        this.getSearchField().searchAble(function (value) {
            self.action('search', {search: value}, function (result) {
                self.setFilesContainer(result.files);
                self.setPath(result.path);
                self.getFileContainer().find('.file .name span').highlight(value);
            })
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

            formData.append('folderId', self.getCurrentFolderId());

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

    setPath: function (path) {
        this.getFinder().find('.path').html(path);
        this.initPath();
    },

    getSearchField: function () {
        return this.getToolbar().find('.search input');
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

    getCurrentFolderId: function () {
        return this.getFinder().find('input.currentFolderId').val();
    },

    saveCurrentFolderId: function (folderId) {
        this.getFinder().find('input.currentFolderId').val(folderId);
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