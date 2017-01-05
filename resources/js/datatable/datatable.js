var DataTable = function () {
};

DataTable.prototype =
{
    instance: null,
    currentSearch: null,

    init: function () {
        this.initTable();
        this.initPagination();
        this.initSearch();
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
            self.action('search', {search: value}, function (result) {
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
        });

        $rows.find('td.edit').click(function () {
            var id = $(this).find('input[name=id]').val();
            self.actionEdit(id);
        });

        $rows.dblclick(function () {
            var id = $(this).find('input[name=id]').val();
            self.actionEdit(id);
        });

        var searchValue = this.getSearchField().val();

        if(searchValue){
            self.getDatatable().find('.table').find('td').highlight(searchValue);
        }
    },

    initWindow: function () {
        var self    = this;
        var $window = this.getWindow();

        $window.find('.closeButton').click(function () {
            self.closeWindow();
        });

        $window.find('.saveAndClose').click(function () {
            self.actionSave(true);
        });

        $window.find('.save').click(function () {
            self.actionSave(false);
        });
    },

    action: function (action, parameters, onSuccess, loadingElement) {
        var self          = this;
        var ajaxCompleted = false;

        parameters.dataTableInstance = self.instance;

        setTimeout(function () {
            if (ajaxCompleted == false) {
                KikCMS.showLoader(loadingElement);
            }
        }, 250);

        $.ajax({
            url: '/cms/datatable/' + action,
            type: 'post',
            dataType: 'json',
            data: parameters,
            success: function (result, responseText, response) {
                ajaxCompleted = true;
                KikCMS.hideLoader(loadingElement);

                onSuccess(result, responseText, response);
            },
            error: function (result, errorType, errorMessage) {
                ajaxCompleted = true;
                KikCMS.hideLoader(loadingElement);

                alert(errorMessage + '\n\n' + $(result.responseText).text());
            }
        });
    },

    actionEdit: function (id) {
        var self = this;

        this.showWindow();

        this.action('edit', {dataTableEditId: id}, function (result) {
            self.setWindowContent(result.window);
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

    closeWindow: function () {
        $('body').removeClass('datatableBlur');
        this.getWindow().fadeOut();
        this.getWindow().find('.windowContent').html('');
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

        return filters;
    },

    getSearchField: function () {
        return this.getDatatable().find('.toolbar .search input');
    },

    getWindow: function () {
        var windowId = this.instance + 'Window';

        if ($('body > #notFading > #' + windowId).length < 1) {
            var $window = '<div class="datatableWindow" id="' + windowId + '">' +
                '<div class="closeButton"></div><div class="windowContent"></div></div>';

            $('body > #notFading').prepend($window);
        }

        return $('#' + windowId);
    }
};