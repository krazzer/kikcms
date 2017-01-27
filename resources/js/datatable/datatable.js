var DataTable = function () {
};

DataTable.prototype =
{
    instance: null,
    labels: null,
    currentSearch: null,
    currentFormInput: null,
    parentEditId: null,

    getDeleteConfirmMessage: function (amount) {
        var confirmText = KikCMS.tl('dataTable.delete.confirmOne');

        if (KikCMS.tl(this.labels + '.deleteOne')) {
            confirmText = KikCMS.tl(this.labels + '.deleteOne')
        }

        if (amount > 1) {
            if (KikCMS.tl(this.labels + '.delete')) {
                confirmText = KikCMS.tl(this.labels + '.delete', {amount: amount});
            } else {
                confirmText = KikCMS.tl('dataTable.delete.confirm', {amount: amount});
            }
        }

        return confirmText;
    },

    getFormSerialized: function () {
        var $formGroups = this.getWindow().find('form > *:not(.type-dataTable) input, select, textarea, form > input');
        return $formGroups.serialize();
    },

    init: function () {
        this.initTable();
        this.initPagination();
        this.initSearch();
        this.initButtons();
    },

    initButtons: function () {
        var self          = this;
        var $deleteButton = this.getDataTable().find('.toolbar .button.delete');
        var $addButton    = this.getDataTable().find('.toolbar .button.add');

        $deleteButton.click(function () {
            var selectedIds = self.getSelectedIds();

            if (selectedIds) {
                var amount      = selectedIds.length;
                var confirmText = self.getDeleteConfirmMessage(amount);

                if (confirm(confirmText)) {
                    self.actionDelete(selectedIds);
                }
            }
        });

        $addButton.click(function () {
            self.actionAdd();
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

            self.action('search', filters, function (result) {
                self.setTableContent(result.table);
                self.setPagesContent(result.pagination);
            })
        });
    },

    initTable: function () {
        var self  = this;
        var $rows = this.getDataTable().find('tbody tr');

        $rows.find('td:not(.edit)').click(function () {
            $(this).parent().toggleClass('selected');
            self.updateToolbar();
        });

        $rows.find('td.edit').click(function () {
            var id = $(this).find('input[name=id]').val();
            self.actionEdit(id);
        });

        $rows.on('dblclick', function () {
            var id = $(this).find('input[name=id]').val();
            self.actionEdit(id);
        });

        var searchValue = this.getSearchField().val();

        if (searchValue) {
            self.getDataTable().find('.table').find('td').highlight(searchValue);
        }

        this.getDataTable().find('thead td').click(function () {
            var $column      = $(this);
            var column       = $column.attr('data-column');
            var curDirection = $column.attr('data-sort');
            var direction    = 'asc';

            if (curDirection == 'asc') {
                direction = 'desc';
            } else if (curDirection == 'desc') {
                direction = '';
            }

            self.actionSort(column, direction);
        });

        self.updateToolbar();
    },

    initWindow: function () {
        var self    = this;
        var $window = this.getWindow();

        KikCMS.initWebForms($window);
        this.initWysiwyg();

        $window.find('.saveAndClose').click(function () {
            self.actionSave(true);
        });

        $window.find('.save').click(function () {
            self.actionSave(false);
        });

        this.currentFormInput = this.getFormSerialized();
        this.initWindowSize();

        $(window).resize(this.initWindowSize.bind(this));
    },

    initWindowSize: function () {
        var $window = this.getWindow();

        var windowHeight = $window.height();
        var headerHeight = $window.find('.windowContent > .header').outerHeight();
        var footerHeight = $window.find('.windowContent > .footer').outerHeight();

        $window.find('.content').css('height', windowHeight - headerHeight - footerHeight);
    },

    initWysiwyg: function () {
        tinymce.init({
            selector: this.getWysiwygSelector(),
            setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave();
                });
            },
            language_url: '/cmsassets/js/tinymce/nl.js',
            language: 'nl',
            theme: 'modern',
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace visualblocks',
                'visualchars code insertdatetime media nonbreaking save table contextmenu directionality template paste',
                'textcolor colorpicker textpattern imagetools codesample toc'
            ],
            toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify' +
            ' | bullist numlist outdent indent | link image | forecolor backcolor | codesample',
            image_advtab: true,
            content_css: ['/cmsassets/css/tinymce/content.css']
        });
    },

    action: function (action, parameters, onSuccess, onError) {
        parameters.dataTableInstance = this.instance;

        if (this.parentEditId != null) {
            parameters.parentEditId = this.parentEditId;
        }

        KikCMS.action('/cms/datatable/' + action, parameters, onSuccess, onError);
    },

    actionAdd: function () {
        var self = this;

        this.showWindow();

        this.action('add', {}, function (result) {
            self.setWindowContent(result.window);
        }, function () {
            self.closeWindow();
        });
    },

    actionDelete: function (ids) {
        var self   = this;
        var params = this.getFilters();

        params.ids = ids;

        this.action('delete', params, function (result) {
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

    actionEdit: function (id) {
        var self = this;

        this.showWindow();

        this.action('edit', {dataTableEditId: id}, function (result) {
            self.setWindowContent(result.window);
        }, function () {
            self.closeWindow();
        });
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

    actionSave: function (closeWindow) {
        var self    = this;
        var $window = this.getWindow();
        var params  = $window.find('form').serializeObject();

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
        });
    },

    actionSort: function (column, direction) {
        var self    = this;
        var filters = this.getFilters();

        filters.sortColumn    = column;
        filters.sortDirection = direction;

        this.action('sort', filters, function (result) {
            self.setTableContent(result.table);
            self.setPagesContent(result.pagination);
        });
    },

    closeWindow: function () {
        var $window = this.getWindow();
        var level   = parseInt($window.attr('data-level'));

        if (level == 0) {
            $('body').removeClass('datatableBlur');
        } else {
            $('.dataTableWindow.level' + (level - 1)).removeClass('blur');
        }

        $('.dataTableWindow.level' + (level + 1)).remove();

        $window.fadeOut();
        tinymce.remove(this.getWysiwygSelector());
        $window.find('.windowContent').html('');

        this.currentFormInput = null;
    },

    showWindow: function () {
        var $window = this.getWindow();
        var level   = parseInt($window.attr('data-level'));

        if (level == 0) {
            $('body').addClass('datatableBlur');
        } else {
            $('.dataTableWindow.level' + (level - 1)).addClass('blur');
        }

        $window.fadeIn();
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

        if (!editedId) {
            return;
        }

        var $editedRow = $table.find("tr[data-id=" + editedId + "]");
        $editedRow.addClass('edited');

        setTimeout(function () {
            $editedRow.addClass('easeOutBgColor');
            $editedRow.removeClass('edited');

            setTimeout(function () {
                $editedRow.removeClass('easeOutBgColor');
            }, 500);
        }, 5000);
    },

    setWindowContent: function (contents) {
        this.getWindow().find('.windowContent').html(contents);
        this.initWindow();
    },

    getCurrentPage: function () {
        var currentPage = this.getDataTable().find('.pagination .active a').attr('data-page');

        if (!currentPage) {
            return 1;
        }

        return currentPage;
    },

    getDataTable: function () {
        return $("#" + this.instance);
    },

    getFilters: function () {
        var filters = {};

        filters.page   = this.getCurrentPage();
        filters.search = this.getSearchField().val();

        this.getDataTable().find('table thead td[data-sort="asc"]').each(function () {
            filters.sortDirection = 'asc';
            filters.sortColumn    = $(this).attr('data-column');
        });

        this.getDataTable().find('table thead td[data-sort="desc"]').each(function () {
            filters.sortDirection = 'desc';
            filters.sortColumn    = $(this).attr('data-column');
        });

        return filters;
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
        var self              = this;
        var windowId          = this.instance + 'Window';
        var $bodyNotFading    = $('body > #notFading');
        var level             = 0;
        var parentWindowLevel = this.getDataTable().parentsUntil('.dataTableWindow').parent().attr('data-level');

        if (parentWindowLevel) {
            level = parseInt(parentWindowLevel) + 1;
            windowId += 'Level' + level;
        }

        if ($bodyNotFading.find(' > #' + windowId).length < 1) {
            var $window = '<div class="dataTableWindow level' + level + '" data-level="' + level + '" id="' + windowId + '">' +
                '<div class="closeButton"></div><div class="windowContent"></div></div>';

            $bodyNotFading.prepend($window);

            $bodyNotFading.find(' > #' + windowId).find('.closeButton').click(function () {
                if (self.currentFormInput != self.getFormSerialized()) {
                    if (!confirm(KikCMS.tl('dataTable.closeWarning'))) {
                        return;
                    }
                }

                self.closeWindow();
            });
        }

        return $('#' + windowId);
    },

    getWysiwygSelector: function () {
        var windowId = this.getWindow().attr("id");
        return '#' + windowId + ' textarea.wysiwyg';
    },

    updateToolbar: function () {
        var $selectedRows = this.getDataTable().find('tr.selected');
        var $deleteButton = this.getDataTable().find('.toolbar .button.delete');

        if ($selectedRows.length > 0) {
            $deleteButton.removeClass('faded');
        } else {
            $deleteButton.addClass('faded');
        }
    }
};