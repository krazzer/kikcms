var Finder = function () {
};

Finder.prototype =
{
    instance: null,
    shiftKeyPressed: false,
    cutFileIds: [],

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

    actionCut: function () {
        this.cutFileIds = this.getSelectedFileIds();
        this.initCutFiles();
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

    actionEditFileName: function ($file) {
        var self = this;

        var fileId          = $file.attr('data-id');
        var currentFileName = KikCMS.removeExtension($file.find('.name span').text());

        var newFileName = prompt(KikCMS.tl('media.editFileName'), currentFileName);

        if (!newFileName) {
            return;
        }

        this.action('editFileName', {fileId: fileId, fileName: newFileName}, function (result) {
            self.setFilesContainer(result.files, result.fileIds);
        });
    },

    actionPaste: function () {
        var self = this;

        this.action('paste', {fileIds: this.cutFileIds}, function (result) {
            self.cutFileIds = [];
            self.setFilesContainer(result.files, result.fileIds);
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
        this.getToolbar().find('.cut').click(this.actionCut.bind(this));
        this.getToolbar().find('.paste').click(this.actionPaste.bind(this));
    },

    initCutFiles: function () {
        var self = this;

        self.getFileContainer().find('.file').removeClass('cut');

        $.each(this.cutFileIds, function (index, fileId) {
            var $file = self.getFileContainer().find('.file-' + fileId);
            $file.addClass('cut');
        });

        this.updateToolbar();
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

            // click name
            $file.find('.name span').click(function () {
                if (!$file.hasClass('selected')) {
                    return;
                }

                setTimeout(function () {
                    if ($file.attr('data-double-clicked')) {
                        return;
                    }

                    self.actionEditFileName($file);
                }, 500);
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
                $file.attr('data-double-clicked', true);

                setTimeout(function () {
                    $file.removeAttr('data-double-clicked');
                }, 500);

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
        this.initCutFiles();
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
                self.setFilesContainer(result.files, result.fileIds);

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

    setFilesContainer: function (html, fileIds) {
        var $filesContainer = this.getFileContainer();

        $filesContainer.html(html);

        this.initFiles();

        $.each(fileIds, function (index, fileId) {
            if (fileId === false) {
                return;
            }

            var $file = $filesContainer.find('.file-' + fileId);
            $file.addClass('edited');

            setTimeout(function () {
                $file.find('.thumb').addClass('easeOutBgColor');
                $file.removeClass('edited');

                setTimeout(function () {
                    $file.find('.thumb').removeClass('easeOutBgColor');
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

    selectedSingleFolder: function () {
        var $selectedFiles = this.getFileContainer().find('.file.selected');

        if ($selectedFiles.length > 1) {
            return false;
        }

        return $selectedFiles.hasClass('folder');
    },

    /**
     * Checks if any toolbar button should be updated by selected files etc
     */
    updateToolbar: function () {
        var self     = this;
        var $toolbar = this.getToolbar();

        if (this.getSelectedFileIds().length >= 1) {
            if (this.selectedSingleFolder()) {
                setTimeout(function () {
                    if (self.getSelectedFileIds().length >= 1) {
                        $toolbar.find('.delete, .copy, .cut').fadeIn();
                    }
                }, 500);
            } else {
                $toolbar.find('.delete, .copy, .cut').fadeIn();
            }
        } else {
            $toolbar.find('.delete, .copy, .cut').fadeOut();
        }

        if (this.cutFileIds.length > 0) {
            $toolbar.find('.paste').fadeIn();
        } else {
            $toolbar.find('.paste').fadeOut();
        }
    }
};