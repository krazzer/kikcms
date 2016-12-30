var DataTable = function () {
};

DataTable.prototype =
{
    instance: null,

    init: function () {
        var self = this;

        this.initTable();
        this.initPagination();
    },

    initPagination: function () {
        var self = this;

        this.getDatatable().find('.pagination a').click(function () {
            var $pageButton = $(this);

            if ($pageButton.parent().hasClass('active') || $pageButton.parent().hasClass('disabled') ) {
                return false;
            }

            var page = $pageButton.html();

            self.actionPage(page);
        });
    },

    initTable: function () {
        var self = this;
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
    },

    initWindow: function () {
        var self = this;
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

    actionEdit: function (id) {
        var self = this;
        var $window = this.getWindow();

        $('body').addClass('datatableBlur');
        $window.fadeIn();

        $.ajax({
            url: '/cms/datatable/edit',
            type: 'post',
            data: {
                dataTableInstance: self.instance,
                dataTableId: id
            },
            success: function (result) {
                $window.find('.windowContent').html(result);
                self.initWindow();
            },
            error: function (result) {
            }
        });
    },

    actionPage: function (page) {
        var self = this;

        $.ajax({
            url: '/cms/datatable/page',
            type: 'post',
            dataType: 'json',
            data: {
                dataTableInstance: self.instance,
                page: page
            },
            success: function (result) {
                self.getDatatable().find('.table').html(result.table);
                self.getDatatable().find('.pagination').html(result.pagination);

                self.initTable();
                self.initPagination();
            },
            error: function (result) {
            }
        });
    },

    actionSave: function (closeWindow) {
        var self = this;
        var $window = this.getWindow();
        var $form = $window.find('form');
        var formContents = $form.serialize();

        $.ajax({
            url: '/cms/datatable/save',
            type: 'post',
            data: formContents,
            success: function (result, responseText, response) {
                if (closeWindow && response.status == 200) {
                    self.closeWindow();
                } else {
                    $window.find('.windowContent').html(result);
                    self.initWindow();
                }
            },
            error: function (result) {
            }
        });
    },

    closeWindow: function () {
        $('body').removeClass('datatableBlur');
        this.getWindow().fadeOut();
        this.getWindow().find('.windowContent').html('');
    },

    getDatatable: function () {
        return $("#" + this.instance);
    },

    getWindow: function () {
        var windowId = this.instance + 'Window';

        if ($('body > #' + windowId).length < 1) {
            var $window = '<div class="datatableWindow" id="' + windowId + '">' +
                '<div class="closeButton"></div><div class="windowContent"></div></div>';

            $('body').prepend($window);
        }

        return $('#' + windowId);
    }
};