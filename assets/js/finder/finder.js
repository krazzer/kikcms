var Finder = Class.extend({
    renderableInstance: null,
    renderableClass: null,
    shiftKeyPressed: false,
    pickingMode: false,
    cutFileIds: [],

    action: function (action, parameters, onSuccess) {
        if (typeof parameters.folderId == 'undefined') {
            parameters.folderId = this.getCurrentFolderId();
        }

        parameters.renderableInstance = this.renderableInstance;
        parameters.renderableClass = this.renderableClass;

        KikCMS.action('/cms/finder/' + action, parameters, onSuccess);
    },

    actionAddFolder: function () {
        var self = this;
        var folderName = prompt(KikCMS.tl('media.createFolder'), KikCMS.tl('media.defaultFolderName'));

        if (!folderName) {
            return;
        }

        this.action('createFolder', {folderName: folderName}, function (result) {
            self.setFilesContainer(result.files, result.fileIds);
        })
    },

    actionCut: function () {
        this.cutFileIds = this.getSelectedFileIds();
        this.initCutFiles();
    },

    actionDelete: function () {
        var self = this;
        var selectedIds = this.getSelectedFileIds();

        var confirmMessage = selectedIds.length > 1
            ? KikCMS.tl('media.deleteConfirm', {amount: selectedIds.length})
            : KikCMS.tl('media.deleteConfirmOne');

        if (!confirm(confirmMessage)) {
            return;
        }

        this.action('delete', {fileIds: selectedIds}, function (result) {
            if (result.errorMessage) {
                alert(result.errorMessage);
            }

            if (result.files) {
                self.setFilesContainer(result.files);
            }
        })
    },

    actionEditFileName: function ($file) {
        var self = this;

        var fileId = $file.attr('data-id');
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
        $file.trigger('selectionChange');

        this.updateToolbar();
    },

    fileSelect: function ($file) {
        $file.addClass('selected');
        $file.trigger('selectionChange');

        this.updateToolbar();
    },

    init: function () {
        this.initUpload();
        this.initFiles();
        this.initKeyEvents();
        this.initButtons();
        this.initSearch();
        this.initPath();
        this.initPermissions();
    },

    initButtons: function () {
        this.getToolbar().find('.delete').click(this.actionDelete.bind(this));
        this.getToolbar().find('.addFolder').click(this.actionAddFolder.bind(this));
        this.getToolbar().find('.cut').click(this.actionCut.bind(this));
        this.getToolbar().find('.paste').click(this.actionPaste.bind(this));
        this.getToolbar().find('.download').click(this.download.bind(this));
        this.getToolbar().find('.rights').click(this.openPermissionModal.bind(this));
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
        var self = this;
        var $fileContainer = this.getFileContainer();
        var $files = $fileContainer.find('.file');

        $files.each(function () {
            var $file = $(this);
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
                if (!self.shiftKeyPressed || self.pickingMode) {
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

                self.pickFile($file);

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
                self.pickFile($file);
            }
        });

        // de-select files if we click somewhere else
        this.getFileContainer().click(function () {
            if (self.shiftKeyPressed) {
                return;
            }

            var $selectedFiles = $fileContainer.find('.file.selected');

            self.fileDeSelect($selectedFiles);
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

    initPermissions: function () {
        var self = this;

        var $permissionModal = this.getPermissionModal();

        var $form = $permissionModal.find('form');

        $permissionModal.find('.save').click(function () {
            var hasIntermediate = false;

            $form.find('input').each(function () {
                if ($(this).prop('indeterminate')) {
                    hasIntermediate = true;
                }
            });

            if (hasIntermediate) {
                $permissionModal.find('.messages .alert').hide();
                $permissionModal.find('.messages .warning').fadeIn();
                return;
            }

            var data = $form.serializeObject();

            data.fileIds = self.getSelectedFileIds();

            self.action('updatePermissions', data, function (response) {
                $permissionModal.find('.messages .alert').hide();

                if (response.success == true) {
                    $permissionModal.find('.messages .success').fadeIn();
                } else {
                    $permissionModal.find('.messages .error').fadeIn();
                }
            });
        });

        $form.on('change', '.check input', function () {
            var $checkbox = $(this);

            if ($checkbox.attr('data-right') == 'write' && $checkbox.prop('checked')) {
                $checkbox.parent().parent().prev().find('input').prop('indeterminate', false);
                $checkbox.parent().parent().prev().find('input').prop('checked', true);
            }

            if ($checkbox.attr('data-right') == 'write' && $checkbox.prop('indeterminate')) {
                $checkbox.parent().parent().prev().find('input').prop('indeterminate', true);
            }

            if ($checkbox.attr('data-right') == 'read' && !$checkbox.prop('checked')) {
                $checkbox.parent().parent().next().find('input').prop('checked', false);
                $checkbox.parent().parent().next().find('input').prop('indeterminate', false);
            }
        });

        $form.on('change', 'select', function () {
            var val = $(this).val();
            var $row = $(this).parent().parent();

            if (!val) {
                if ($row.next().length) {
                    $row.remove();
                }

                return;
            }

            // add a new row if there isn't one already
            if (!$row.next().length) {
                var $newRow = $row.clone();
                $form.find('table').append($newRow);
            }

            $row.find('input').removeAttr('disabled');
            $row.find('input:first').attr('name', 'permission[' + val + '][read]');
            $row.find('input:last').attr('name', 'permission[' + val + '][write]');
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
        var self = this;

        var uploader = new FinderFileUploader({
            $container: this.getFinder(),
            onSuccess: function (result) {
                self.setFilesContainer(result.files, result.fileIds);
            },
            addParametersBeforeUpload: function (formData) {
                formData.append('folderId', self.getCurrentFolderId());
                formData.append('renderableInstance', self.renderableInstance);
                formData.append('renderableClass', self.renderableClass);
                return formData;
            }
        });

        uploader.init();
    },

    download: function () {
        this.getSelectedFiles().trigger('dblclick');
    },

    getFinder: function () {
        return $("#" + this.renderableInstance);
    },

    getFileContainer: function () {
        return this.getFinder().find('.files .files-container');
    },

    getPermissionModal: function () {
        return this.getFinder().find('.permissionModal');
    },

    pickFile: function ($file) {
        if (!this.pickingMode) {
            window.open($file.attr('data-url'));
        } else {
            $file.trigger("pick");
        }
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

        this.getSelectedFiles().each(function () {
            ids.push($(this).attr('data-id'));
        });

        return ids;
    },

    /**
     * @returns {*}
     */
    getSelectedFiles: function () {
        return this.getFileContainer().find('.file.selected');
    },

    getToolbar: function () {
        return this.getFinder().find('.toolbar');
    },

    /**
     * @returns int
     */
    getCurrentFolderId: function () {
        return this.getFinder().find('input.currentFolderId').val();
    },

    /**
     * Open the modal window to manage file permissions
     */
    openPermissionModal: function () {
        var $modal = this.getPermissionModal();
        var $files = this.getSelectedFiles();

        this.action('getPermissionData', {fileIds: this.getSelectedFileIds()}, function (response) {
            $modal.find('.modal-title .file').html(response.title);

            $modal.find('input').prop('indeterminate', false);
            $modal.find('input').prop('checked', false);

            $modal.find('.messages .alert').hide();

            var $subFileCheckbox = $modal.find('.sub-files-checkbox');

            if ($files.hasClass('folder')) {
                $subFileCheckbox.show();
            } else {
                $subFileCheckbox.hide();
            }

            // remove all users with a value
            $modal.find('select').each(function () {
                if ($(this).val() || $modal.find('select').length > 1) {
                    $(this).parent().parent().remove();
                }
            });

            var $lastRow = $modal.find('table tr:last');

            $.each(response.table, function (key, permission) {
                if (isNumeric(key)) {
                    var $row = $lastRow.clone();

                    $row.find('select').val(key);
                    $row.find('input').removeAttr('disabled');
                    $row.find('input:first').attr('name', 'permission[' + key + '][read]').attr('data-right', 'read');
                    $row.find('input:last').attr('name', 'permission[' + key + '][write]').attr('data-right', 'write');

                    $lastRow.before($row);
                }

                $.each(permission, function (type, value) {
                    var $checkbox = $('input[name="permission[' + key + '][' + type + ']"]');

                    switch (value) {
                        case 2:
                            $checkbox.prop("indeterminate", true).trigger('change');
                            break;
                        case 1:
                            $checkbox.prop("checked", true).trigger('change');
                            break;
                    }
                });
            });

            $modal.modal();
        });
    },

    saveCurrentFolderId: function (folderId) {
        this.getFinder().find('input.currentFolderId').val(folderId);
    },

    /**
     * @returns bool
     */
    selectedSingleFolder: function () {
        var $selectedFiles = this.getSelectedFiles();

        if ($selectedFiles.length > 1) {
            return false;
        }

        return $selectedFiles.hasClass('folder');
    },

    /**
     * Checks if any toolbar button should be updated by selected files etc
     */
    updateToolbar: function () {
        var self = this;
        var $toolbar = this.getToolbar();

        if (this.getSelectedFileIds().length === 1 && !this.selectedSingleFolder()) {
            $toolbar.find('.download').fadeIn();
        } else {
            $toolbar.find('.download').fadeOut();
        }

        if (this.getSelectedFileIds().length >= 1) {
            if (this.selectedSingleFolder()) {
                setTimeout(function () {
                    if (self.getSelectedFileIds().length >= 1) {
                        $toolbar.find('.delete, .cut').fadeIn();
                    }
                }, 250);
            } else {
                $toolbar.find('.delete, .cut').fadeIn();
            }

            $toolbar.find('.rights').fadeIn();
        } else {
            $toolbar.find('.delete, .cut, .rights').fadeOut();
        }

        if (this.cutFileIds.length > 0) {
            $toolbar.find('.paste').fadeIn();
        } else {
            $toolbar.find('.paste').fadeOut();
        }
    }
});