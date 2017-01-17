var DataTable = function () {
};

DataTable.prototype =
{
    instance: null,
    currentSearch: null,
    currentFormInput: null,

    init: function () {
        this.initTable();
        this.initPagination();
        this.initSearch();
        this.initButtons();
    },

    initAutocomplete: function () {
        var self = this;

        this.getWindow().find('.autocomplete').each(function () {
            var $field   = $(this);
            var fieldKey = $field.attr('data-field-key');

            self.action('getAutocompleteData', {field: fieldKey}, function (data) {
                $field.typeahead({
                    items: 30,
                    source: data
                });
            });
        });
    },

    initButtons: function () {
        var self          = this;
        var $deleteButton = this.getDatatable().find('.toolbar .button.delete');
        var $addButton    = this.getDatatable().find('.toolbar .button.add');

        $deleteButton.click(function () {
            var selectedIds = self.getSelectedIds();

            if (selectedIds) {
                var amount      = selectedIds.length;
                var confirmText = KikCMS.tl('dataTable.delete.confirmOne');

                if (amount > 1) {
                    confirmText = KikCMS.tl('dataTable.delete.confirmMultiple', {amount: amount});
                }

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

        this.getDatatable().find('.pagination a').click(function () {
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
                self.getDatatable().find('.table').html(result.table);
                self.getDatatable().find('.pages').html(result.pagination);

                self.initTable();
                self.initPagination();
            })
        });
    },

    initTable: function () {
        var self  = this;
        var $rows = this.getDatatable().find('tbody tr');

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
            self.getDatatable().find('.table').find('td').highlight(searchValue);
        }

        this.getDatatable().find('thead td').click(function () {
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

        this.initAutocomplete();
        this.initWysiwyg();

        $window.find('.saveAndClose').click(function () {
            self.actionSave(true);
        });

        $window.find('.save').click(function () {
            self.actionSave(false);
        });

        this.currentFormInput = $window.find('form').serialize();
        this.initWindowSize();

        $(window).resize(this.initWindowSize.bind(this));
    },

    initWindowSize: function () {
        var $window = this.getWindow();

        var windowHeight = $window.height();
        var headerHeight = $window.find('.header').outerHeight();
        var footerHeight = $window.find('.footer').outerHeight();

        $window.find('.content').css('height', windowHeight - headerHeight - footerHeight);
    },

    initWysiwyg: function () {
        tinymce.remove(this.getWysiwygSelector());

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
        var self          = this;
        var ajaxCompleted = false;
        var retries       = 0;

        parameters.dataTableInstance = self.instance;

        setTimeout(function () {
            if (ajaxCompleted == false) {
                KikCMS.showLoader();
            }
        }, 250);

        var xmlHttpRequest = function () {
            $.ajax({
                url: '/cms/datatable/' + action,
                type: 'post',
                dataType: 'json',
                data: parameters,
                success: function (result, responseText, response) {
                    ajaxCompleted = true;
                    KikCMS.hideLoader();

                    onSuccess(result, responseText, response);
                },
                error: function (result) {
                    // try again on connection failure
                    if (result.readyState == 0 && result.status == 0 && retries < 2) {
                        retries++;
                        xmlHttpRequest();
                        return;
                    }

                    if (typeof(onError) != 'undefined') {
                        onError();
                    }

                    ajaxCompleted = true;
                    KikCMS.hideLoader();

                    var key = KikCMS.errorMessages[result.status] ? result.status : 'unknown';

                    if (KikCMS.isDev && result.status != 440) {
                        $("#ajaxDebugger").html(result.responseText).show();
                    } else {
                        alert(KikCMS.errorMessages[key].title + "\n\n" + KikCMS.errorMessages[key].description);
                    }
                }
            });
        };

        xmlHttpRequest();
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
            self.getDatatable().find('.table').html(result.table);
            self.getDatatable().find('.pages').html(result.pagination);

            self.initTable();
            self.initPagination();
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
            self.getDatatable().find('.table').html(result.table);
            self.getDatatable().find('.pages').html(result.pagination);

            self.initTable();
            self.initPagination();
        });
    },

    actionSave: function (closeWindow) {
        var self    = this;
        var $window = this.getWindow();
        var $form   = $window.find('form');
        var params  = $form.serializeObject();

        $.extend(params, this.getFilters());

        this.action('save', params, function (result, responseText, response) {
            if (response.status == 200) {
                self.setTableContent(result.table, result.editedId);
            }

            if (closeWindow && response.status == 200) {
                self.closeWindow();
            } else {
                self.setWindowContent(result.window);
            }
        });
    },

    actionSort: function (column, direction) {
        var self    = this;
        var filters = this.getFilters();

        filters.sortColumn    = column;
        filters.sortDirection = direction;

        this.action('sort', filters, function (result) {
            self.getDatatable().find('.table').html(result.table);
            self.getDatatable().find('.pages').html(result.pagination);

            self.initTable();
            self.initPagination();
        });
    },

    closeWindow: function () {
        var $window = this.getWindow();

        $('body').removeClass('datatableBlur');

        $window.fadeOut();
        $window.find('.windowContent').html('');

        this.currentFormInput = null;
    },

    showWindow: function () {
        $('body').addClass('datatableBlur');
        this.getWindow().fadeIn();
    },

    setTableContent: function (tableContent, editedId) {
        var $table = this.getDatatable().find('.table');
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
        var currentPage = this.getDatatable().find('.pagination .active a').attr('data-page');

        if (!currentPage) {
            return 1;
        }

        return currentPage;
    },

    getDatatable: function () {
        return $("#" + this.instance);
    },

    getFilters: function () {
        var filters = {};

        filters.page   = this.getCurrentPage();
        filters.search = this.getSearchField().val();

        this.getDatatable().find('table thead td[data-sort="asc"]').each(function () {
            filters.sortDirection = 'asc';
            filters.sortColumn    = $(this).attr('data-column');
        });

        this.getDatatable().find('table thead td[data-sort="desc"]').each(function () {
            filters.sortDirection = 'desc';
            filters.sortColumn    = $(this).attr('data-column');
        });

        return filters;
    },

    getSearchField: function () {
        return this.getDatatable().find('.toolbar .search input');
    },

    getSelectedIds: function () {
        var ids = [];

        this.getDatatable().find('tr.selected .edit input[name=id]').each(function () {
            ids.push($(this).val());
        });

        return ids;
    },

    getWindow: function () {
        var self           = this;
        var windowId       = this.instance + 'Window';
        var $bodyNotFading = $('body > #notFading');

        if ($bodyNotFading.find(' > #' + windowId).length < 1) {
            var $window = '<div class="datatableWindow" id="' + windowId + '">' +
                '<div class="closeButton"></div><div class="windowContent"></div></div>';

            $bodyNotFading.prepend($window);

            $bodyNotFading.find(' > #' + windowId).find('.closeButton').click(function () {
                if (self.currentFormInput != self.getWindow().find('form').serialize()) {
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
        var $selectedRows = this.getDatatable().find('tr.selected');
        var $deleteButton = this.getDatatable().find('.toolbar .button.delete');

        if ($selectedRows.length > 0) {
            $deleteButton.removeClass('faded');
        } else {
            $deleteButton.addClass('faded');
        }
    }
};