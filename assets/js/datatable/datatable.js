var DataTable = Class.extend({
    actionPath: '/cms/datatable/',
    renderableInstance: null,
    renderableClass: null,
    labels: null,
    currentSearch: null,
    currentFormInput: null,
    parentEditId: null,
    parentModel: null,
    parentRelationKey: null,
    sortDirection: null,
    sortColumn: null,
    restore: null,
    filePicker: null,
    lastSelectedRow: null,
    $table: null,

    getDeleteConfirmMessage: function (amount) {
        var confirmText = capitalize(KikCMS.tl('dataTable.delete.confirmOne', {itemSingular: this.labels[0]}));

        if (amount > 1) {
            confirmText = KikCMS.tl('dataTable.delete.confirm', {amount: amount, itemPlural: this.labels[1]});
        }

        return confirmText;
    },

    getFormGroups: function () {
        return this.getWindow().find('form :input').not('.datatable :input');
    },

    getFormSerialized: function () {
        return this.getFormGroups().serialize();
    },

    getThumbHoverContainer: function () {
        var thumbHoverSelector = 'body > .datatableThumbHoverContainer';

        if ($(thumbHoverSelector).length === 0) {
            $('body').append('<div class="datatableThumbHoverContainer"></div>');
        }

        return $(thumbHoverSelector);
    },

    init: function () {
        this.initTable();
        this.initPagination();
        this.initSearch();
        this.initLanguageSwitch();
        this.initButtons();
        this.initKeyEvents();
        this.initFilters();
        this.initRestore();
    },

    /**
     * Initialize toolbar buttons
     */
    initButtons: function () {
        var self             = this;
        var $deleteButton    = this.getDataTable().find('.toolbar .btn.delete');
        var $addButton       = this.getDataTable().find('.toolbar .btn.add');
        var $pickImageButton = this.getDataTable().find('.toolbar .btn.pick-image');
        var $uploadButton    = this.getDataTable().find('.toolbar .btn.upload.direct-image-upload');

        $deleteButton.click(function () {
            if ($(this).attr('disabled') == 'disabled') {
                return;
            }

            var selectedIds = self.getSelectedIds();

            if (selectedIds) {
                var amount      = selectedIds.length;
                var confirmText = self.getDeleteConfirmMessage(amount);

                if (confirm(confirmText)) {
                    self.actionDelete(selectedIds);
                }
            }
        });

        $pickImageButton.click(function () {
            self.actionPickImage();
        });

        $addButton.click(function () {
            self.actionAdd();
        });

        var uploader = new FileUploader({
            $container: $uploadButton,
            $uploadButton: $uploadButton,
            action: '/cms/datatable/uploadImages',
            fileTypes: KikCMS.allowedExt,
            addParametersBeforeUpload: function (formData) {
                $.each(self.addActionParameters({}), function (key, value){
                    formData.append(key, value);
                });

                return formData;
            },
            onSuccess: function (result) {
                if (result.errors) {
                    alert(result.errors.join("\n\n"));
                    return;
                }

                self.setTableContent(result.table, result.editedIds);
                self.setPagesContent(result.pagination);
            }
        });

        uploader.init();
    },

    initFilters: function () {
        var self = this;

        this.getFilterForm().find('input, select').change(function () {
            self.actionPage(1);
        });
    },

    initImageThumbs: function () {
        var self    = this;
        var $thumbs = this.getDataTable().find('table tr td .thumb');

        var positionThumb = function (e) {
            var scrollTop  = $(window).scrollTop();
            var scrollLeft = $(window).scrollLeft();

            var left = e.clientX + scrollLeft + 15;
            var top  = e.clientY + scrollTop + 15;

            self.getThumbHoverContainer().css({left: left, top: top});
        };

        $thumbs.each(function () {
            var $thumb = $(this);
            var $cell  = $thumb.parent();

            $cell.hover(function (e) {
                var $thumbHoverContainer = self.getThumbHoverContainer();

                positionThumb(e);

                var thumbUrl = $thumb.attr('data-thumb-url');
                var isSvg    = thumbUrl.split("?")[0].endsWith('.svg');

                $thumbHoverContainer.toggleClass('svg', isSvg);
                $thumbHoverContainer.show();
                $thumbHoverContainer.html('<img alt="thumb" src="' + thumbUrl + '" />');
            }, function () {
                self.getThumbHoverContainer().hide();
            });

            $cell.mousemove(positionThumb);
        });

        $thumbs.click(function (e) {
            window.open($(this).attr('data-url'));
            e.stopPropagation();
        });
    },

    initKeyEvents: function () {
        var self    = this;
        var iframes = {};

        var keyDownEvent = function (e) {
            if ((e.metaKey || e.ctrlKey) && e.keyCode == keyCode.S) {
                if ( ! self.windowIsActive() || ! self.getForm().length || ! self.getWindow().find('.saveAndClose').length) {
                    return true;
                }

                self.actionSave(true);
                self.getWindow().find('.saveAndClose').addClass('active');
                e.preventDefault();
                return false;
            }
        };

        var keyPressEvent = function (e) {
            if (e.keyCode == keyCode.ESCAPE) {
                if ( ! self.windowIsActive() || ! self.getForm().length) {
                    return true;
                }

                self.attemptToCloseWindow();
            }
        };

        var onFindingIframe = function () {
            var id = $(this).attr('id');

            if (iframes[id]) {
                return;
            }

            iframes[id] = id;

            var $iframeDocument = $(this.contentWindow.document);
            $iframeDocument.keydown(keyDownEvent);
        };

        var checkIframe = function () {
            $('body').find('iframe').each(onFindingIframe);
            setTimeout(checkIframe, 1000);
        };

        checkIframe();

        // unbind any previously bindings for this instance
        $(window).unbind('keydown.' + this.renderableInstance);
        $(window).unbind('keypress.' + this.renderableInstance);

        $(window).bind('keydown.' + this.renderableInstance, keyDownEvent);
        $(window).bind('keypress.' + this.renderableInstance, keyPressEvent);
    },

    initLanguageSwitch: function () {
        var self = this;

        this.getDataTable().find('.language select').change(function () {
            self.actionPage(self.getFilters().page);
        });
    },

    initPagination: function () {
        var self = this;

        this.getDataTable().find('.pagination a').click(function () {
            var $pageButton = $(this);

            if ($pageButton.parent().hasClass('active') || $pageButton.parent().hasClass('disabled')) {
                return false;
            }

            var page = $pageButton.attr('data-page');

            self.actionPage(page);
        });
    },

    initSearch: function () {
        var self = this;

        this.getSearchField().searchAble(function (value) {
            var filters    = self.getFilters();
            filters.search = value;
            filters.page   = 1;

            self.action('search', filters, function (result) {
                self.setTableContent(result.table);
                self.setPagesContent(result.pagination);
            })
        });
    },

    initSort: function () {
        var self        = this;
        var sortControl = new SortControl();

        sortControl.$dataTable = this.getDataTable();

        sortControl.onDrop = function (id, targetId, position) {
            self.action('rearrange', {id: id, targetId: targetId, position: position}, function (result) {
                self.setTableContent(result.table);
            })
        };

        sortControl.init();
    },

    initTable: function () {
        this.$table = this.getDataTable().find('table');

        var self  = this;
        var $rows = this.$table.find('tbody tr');

        $rows.find('td a').click(function (e) {
            e.stopPropagation();
        });

        $rows.find('td:not(.action)').click(function (e) {
            var $row = $(this).parent();

            if ($row.attr('data-prevent-click')) {
                return;
            }

            self.onRowClick($row, e);
        });

        $rows.find('td.edit').click(function () {
            var id = $(this).find('input[name=id]').val();
            self.actionEdit(id);
        });

        $rows.find('td.delete').click(function () {
            var id = $(this).attr('data-id');

            if (confirm(self.getDeleteConfirmMessage(1))) {
                self.actionDelete([id]);
            }
        });

        $rows.find('td:not(.action)').on('dblclick', function () {
            self.onRowDblClick($(this).parent());
        });

        var searchValue = this.getSearchField().val();

        if (searchValue) {
            self.getDataTable().find('.table').find('td').highlight(searchValue);
        }

        this.getDataTable().find('thead td').click(function () {
            var $column      = $(this);
            var column       = $column.attr('data-column');
            var curDirection = $column.attr('data-sort');

            var direction;

            switch (curDirection) {
                case 'asc':
                    direction = 'desc';
                    break;
                case 'desc':
                    direction = '';
                    column    = '';
                    break;
                default:
                    direction = 'asc';
                    break;
            }

            self.sortDirection = direction;
            self.sortColumn    = column;

            self.actionSort(column, direction);
        });

        this.initImageThumbs();
        this.updateToolbar();
        this.initTableCheckBoxes();

        if (typeof SortControl !== 'undefined') {
            this.initSort();
        }
    },

    initTableCheckBoxes: function () {
        var self = this;

        this.$table.find('input.table-checkbox[type=checkbox]').click(function (e) {
            e.stopPropagation();
        }).dblclick(function (e) {
            e.stopPropagation();
        }).change(function () {
            var $checkbox = $(this);
            var checked   = $checkbox.is(":checked");
            var editId    = $checkbox.parent().parent().attr('data-id');
            var column    = $checkbox.attr('data-col');

            $checkbox.attr('readonly', 'readonly');

            self.action('checkCheckbox', {
                editId: editId,
                column: column,
                checked: checked ? 1 : 0
            }, function () {
                $checkbox.removeAttr('readonly')
            }, function () {
                $checkbox.removeAttr('readonly');
            })
        });
    },

    initTabs: function () {
        var $window      = this.getWindow();
        var $tabContents = $window.find('.tab-contents');

        $window.find('.tabs .tab').each(function () {
            var $tab        = $(this);
            var tabKey      = $tab.attr('data-tab');
            var $tabContent = $tabContents.find('.tab-' + tabKey);

            $tab.click(function () {
                $tab.siblings().removeClass('active');
                $tab.addClass('active');

                $tabContents.find('.tab-content').removeClass('active');
                $tabContent.addClass('active');
            });

            if ($tabContent.find('.has-error').length > 0) {
                $tab.addClass('error');
            }
        });
    },

    initRestore: function () {
        this.restore = new DataTableRestore(this);
    },

    initWindow: function () {
        var self        = this;
        var $window     = this.getWindow();
        var $langSelect = $window.find('.header select[name=language]');

        this.initWindowSize();
        this.initTabs();

        $window.find('.saveAndClose').click(function () {
            self.actionSave(true);
        });

        $window.find('.save').click(function () {
            self.actionSave(false);
        });

        this.onChange($langSelect, true, function () {
            self.actionReloadWindow();
        });

        this.currentFormInput = this.getFormSerialized();

        $(window).resize(this.initWindowSize.bind(this));

        this.restore.startPolling();
    },

    initWindowSize: function () {
        var $window = this.getWindow();
        var $footer = $window.find('.windowContent > .footer');

        var windowHeight = $window.height();
        var headerHeight = $window.find('.windowContent > .header').outerHeight();
        var tabsHeight   = 0;

        var footerHeight = $footer.length ? $footer.outerHeight() : 0;

        if ($window.find('.windowContent > .tabs').length > 0) {
            tabsHeight = $window.find('.windowContent > .tabs').outerHeight();
        }

        $window.find('.content').css('height', windowHeight - headerHeight - footerHeight - tabsHeight);
    },

    action: function (action, parameters, onSuccess, onError) {
        parameters = this.addActionParameters(parameters);
        KikCMS.action(this.actionPath + action, parameters, onSuccess, onError, null, this);
    },

    actionAdd: function (extraParams, onReload) {
        var self   = this;
        var params = this.getFilters();

        if (typeof (extraParams) !== 'undefined') {
            for (var key in extraParams) {
                params[key] = extraParams[key];
            }
        }

        this.showWindow();

        this.action('add', params, function (result) {
            self.setWindowContent(result.window, onReload);
        }, function () {
            self.closeWindow();
        });
    },

    actionDelete: function (ids) {
        var self   = this;
        var params = this.getFilters();

        params.ids = ids;

        this.action('delete', params, function (result) {
            if (result.error) {
                alert(result.error);
                return;
            }

            var currentPage = parseInt(params.page);

            // if everything of the current page is removed, go back one page
            if (currentPage > 1 && $(result.table).hasClass('no-data')) {
                self.actionPage(currentPage - 1);
                return;
            }

            self.setTableContent(result.table);
            self.setPagesContent(result.pagination);
        });
    },

    actionEdit: function (id, onReload) {
        var self    = this;
        var filters = this.getFilters();

        this.showWindow();

        filters.editId = id;

        this.action('edit', filters, function (result) {
            self.setWindowContent(result.window, onReload);
        }, function () {
            self.closeWindow();
        });
    },

    actionReloadWindow: function (onReload) {
        var editId = this.getFormEditId();

        if (editId) {
            this.actionEdit(editId, onReload);
        } else {
            this.actionAdd({}, onReload);
        }
    },

    actionPage: function (page) {
        var self    = this;
        var filters = this.getFilters();

        filters.page = page;

        this.action('page', filters, function (result) {
            self.setTableContent(result.table);
            self.setPagesContent(result.pagination);
        });
    },

    /**
     * Pick an image directly to add as a row to the DataTable
     */
    actionPickImage: function () {
        var self = this;

        var onPickFile = function ($file) {
            self.onPickFile($file)
        };

        this.filePicker = new FilePicker(this.renderableInstance, this.getDataTable(), onPickFile, true);
        this.filePicker.open();
    },

    actionReload: function (onSuccess) {
        var self    = this;
        var filters = this.getFilters();

        this.action('page', filters, function (result) {
            self.setTableContent(result.table);
            self.setPagesContent(result.pagination);

            if (typeof onSuccess !== 'undefined') {
                onSuccess();
            }
        });
    },

    actionSave: function (closeWindow) {
        var self    = this;
        var $window = this.getWindow();
        var params  = this.getFormGroups().serializeObject();

        // if a file has been selected, then auto-pick that file
        var $selectedFile = $window.find('.file-picker:visible .file.selected');

        if ($selectedFile.length > 0) {
            $selectedFile.trigger('pick', function () {
                self.actionSave(closeWindow);
            });
            return;
        }

        var $saveButtons = $window.find('.saveAndClose, .save');
        $saveButtons.attr('disabled', 'disabled');

        if (this.getFormEditId()) {
            params.editId = this.getFormEditId();
        }

        $.extend(params, this.getFilters());

        this.action('save', params, function (result, responseText, response) {
            if (response.status == 200) {
                self.setTableContent(result.table, result.editedId);
                self.setPagesContent(result.pagination);
            }

            if (closeWindow && response.status == 200) {
                self.closeWindow();
            } else {
                self.setWindowContent(result.window);
                $window.find('.alert').hide().fadeIn();
            }

            $saveButtons.removeAttr('disabled');
        }, function () {
            $saveButtons.removeAttr('disabled');
        });
    },

    actionSort: function (column, direction) {
        var self    = this;
        var filters = this.getFilters();

        filters.sortColumn    = column;
        filters.sortDirection = direction;
        filters.page          = 1;

        this.action('sort', filters, function (result) {
            self.setTableContent(result.table);
            self.setPagesContent(result.pagination);
        });
    },

    addActionParameters: function (parameters) {
        parameters.renderableInstance = this.renderableInstance;
        parameters.renderableClass    = this.renderableClass;

        if (this.parentEditId != null) {
            parameters.parentEditId = this.parentEditId;
        }

        if (this.parentModel != null) {
            parameters.parentModel = this.parentModel;
        }

        if (this.parentRelationKey != null) {
            parameters.parentRelationKey = this.parentRelationKey;
        }

        var currentTab = this.getWindow().find('.tabs .tab.active').attr('data-tab');

        if (typeof currentTab !== 'undefined') {
            parameters.currentTab = currentTab;
        }

        return parameters;
    },

    attemptToCloseWindow: function () {
        // window is closing
        if (this.currentFormInput == null) {
            return;
        }

        if (this.contentHasChanged()) {
            if ( ! confirm(KikCMS.tl('dataTable.closeWarning'))) {
                return;
            }
        }

        this.closeWindow();
    },

    closeWindow: function () {
        var self = this;

        this.restore.stopPolling();

        KikCMS.windowManager.closeWindow(this.getWindow(), function () {
            $('.datatableThumbHoverContainer').remove();

            if (typeof (tinymce) !== 'undefined') {
                tinymce.remove(self.getWysiwygSelector());
            }

            this.currentFormInput = null;
        });
    },

    /**
     * @return {boolean}
     */
    contentHasChanged: function () {
        return this.currentFormInput != this.getFormSerialized();
    },

    /**
     * Action when files are picked
     * @param $files
     */
    onPickFile: function ($files) {
        var self = this;

        var fileIds = $files.map(function () {
            return $(this).data('id');
        }).get();

        this.action('addImage', {fileIds: fileIds}, function (result) {
            if (result.errors) {
                alert(result.errors.join("\n\n"));
                return;
            }

            self.setTableContent(result.table, result.editedIds);
            self.setPagesContent(result.pagination);
        });
    },

    /**
     * @param $row
     * @param e
     */
    onRowClick: function ($row, e) {
        if (e.shiftKey) {
            if ( ! this.lastSelectedRow) {
                $row.toggleClass('selected');
            } else {
                var indexCurrent = this.getRows().index($row);
                var indexLast    = this.getRows().index(this.lastSelectedRow);

                if(indexCurrent > indexLast){
                    this.lastSelectedRow.nextUntil($row).add($row).addClass('selected');
                } else {
                    $row.nextUntil(this.lastSelectedRow).add($row).addClass('selected');
                }
            }
        } else {
            $row.toggleClass('selected');
        }

        this.lastSelectedRow = $row;

        this.updateToolbar();
    },

    onRowDblClick: function ($row) {
        var id = $row.find('input[name=id]').val();
        this.actionEdit(id);
    },

    showWindow: function () {
        KikCMS.windowManager.showWindow(this.getWindow());
    },

    setEdited: function (rowId) {
        var self = this;

        if (Array.isArray(rowId)) {
            $.each(rowId, function (i, subRowId) {
                var $editedRow = self.getDataTable().find("table tr[data-id=" + subRowId + "]");
                $editedRow.addClass('edited');
            });

            $editedRow = self.getDataTable().find("table tr[data-id=" + rowId[0] + "]");
        } else {
            var $editedRow = this.getDataTable().find("table tr[data-id=" + rowId + "]");
            $editedRow.addClass('edited');
        }

        setTimeout(function () {
            $editedRow.addClass('easeOutBgColor');
            $editedRow.removeClass('edited');

            setTimeout(function () {
                $editedRow.removeClass('easeOutBgColor');
            }, 500);
        }, 5000);

        if ( ! $editedRow.length) {
            return;
        }

        // less relevant for subdatatables, so not implemented
        if (this.isSubDataTable()) {
            return;
        }

        // scroll to the edited row if not visible, with a margin of 250px
        var editedRowY = $editedRow.offset().top;

        var windowHeight = $(window).height();
        var scrollTop    = $(window).scrollTop();

        if (editedRowY > scrollTop + windowHeight - 250 || editedRowY < scrollTop) {
            $('body').animate({scrollTop: editedRowY - 250}, 1000);
        }
    },

    setPagesContent: function (pagesContent) {
        var $pagination = this.getDataTable().find('.pages');
        $pagination.html(pagesContent);

        this.initPagination();
    },

    setTableContent: function (tableContent, editedId) {
        var $table = this.getDataTable().find('.table');
        $table.html(tableContent);

        this.initTable();

        this.lastSelectedRow = null;

        if ( ! editedId) {
            return;
        }

        this.setEdited(editedId);
    },

    /**
     * @param contents
     * @param onReload
     */
    setWindowContent: function (contents, onReload) {
        this.getWindow().find('.windowContent').html(contents);

        if (typeof onReload !== 'undefined') {
            onReload();
        }

        this.initWindow();
    },

    /**
     * @return string
     */
    getClassWithoutSlashes: function () {
        return this.renderableClass.replace(/\\/g, '');
    },

    getCurrentPage: function () {
        var currentPage = this.getDataTable().find('.pagination .active a').attr('data-page');

        if ( ! currentPage) {
            return 1;
        }

        return currentPage;
    },

    getDataTable: function () {
        return $("#" + this.renderableInstance);
    },

    getFilters: function () {
        var filters = {};

        filters.page   = this.getCurrentPage();
        filters.search = this.getSearchField().val();

        if (this.sortColumn && this.sortDirection) {
            filters.sortDirection = this.sortDirection;
            filters.sortColumn    = this.sortColumn;
        }

        var languageCode       = this.getLanguageCode();
        var windowLanguageCode = this.getWindowLanguageCode();

        if (languageCode) {
            filters.languageCode = languageCode;
        }

        if (windowLanguageCode) {
            filters.windowLanguageCode = windowLanguageCode;
        }

        filters.customFilterValues = this.getFilterForm().find(':input').serializeObject();

        return filters;
    },

    getFilterForm: function () {
        return this.getDataTable().find(' > .filters');
    },

    getForm: function () {
        return this.getWindow().find('form');
    },

    getFormEditId: function () {
        return this.getWindow().find('.webForm').attr('data-id');
    },

    getLanguageCode: function () {
        return this.getDataTable().find('.toolbar .language select').val();
    },

    getRows: function () {
        return this.getDataTable().find('table tr');
    },

    getWindowLanguageCode: function () {
        return this.getWindow().find('.header select[name=language]').val();
    },

    getSearchField: function () {
        return this.getDataTable().find('.toolbar .search input');
    },

    getSelectedIds: function () {
        var ids = [];

        this.getDataTable().find('tr.selected .edit input[name=id]').each(function () {
            ids.push($(this).val());
        });

        return ids;
    },

    getWindow: function () {
        return KikCMS.windowManager.getWindow(this.renderableInstance, this.getDataTable(), this.attemptToCloseWindow.bind(this));
    },

    getWysiwygSelector: function () {
        var webFormId = this.getWindow().find('.webForm').attr("id");
        return '#' + webFormId + ' textarea.wysiwyg';
    },

    /**
     * @return {boolean}
     */
    isSubDataTable: function () {
        return this.getDataTable().parents('.dataTableWindow').length >= 1;
    },

    /**
     * Execute given onChange event on given field, but warn the user if the form's input has changed
     *
     * @param $field
     * @param warning
     * @param onChange
     */
    onChange: function ($field, warning, onChange) {
        var self = this;

        var currentValue;
        var formSerialized;

        warning = typeof warning === 'undefined' ? true : warning;

        $field.focus(function () {
            currentValue   = $field.val();
            formSerialized = self.getFormSerialized();
        }).change(function () {
            if (warning) {
                if (self.currentFormInput != formSerialized && ! confirm(KikCMS.tl('dataTable.switchWarning'))) {
                    $field.val(currentValue);
                    return;
                }
            }

            onChange();
        });
    },

    updateToolbar: function () {
        var $selectedRows = this.getDataTable().find('tr.selected');
        var $deleteButton = this.getDataTable().find('.toolbar .btn.delete');

        if ($selectedRows.length > 0) {
            $deleteButton.removeAttr('disabled');
        } else {
            $deleteButton.attr('disabled', 'disabled');
        }
    },

    /**
     * @return {boolean}
     */
    windowIsActive: function () {
        return ! this.getWindow().hasClass('blur');
    }
});