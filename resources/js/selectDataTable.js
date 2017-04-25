var SelectDataTable = DataTable.extend({
    getFilters: function () {
        var filters = this.$.getFilters.call(this);

        filters.selectedValues = this.getSelectionFromInput();

        return filters;
    },

    getInputField: function () {
        return this.getDataTable().next();
    },

    getSelectionFromInput: function () {
        var selection = [];

        if (this.getInputField().val()) {
            selection = JSON.parse(this.getInputField().val());
        }

        return selection;
    },

    initTable: function () {
        this.$.initTable.call(this);

        this.selectCheckBoxes();
        this.initCheckBoxes();
    },

    initCheckBoxes: function () {
        var self = this;

        this.getDataTable().find('td.select input').change(function () {
            var $checkBox = $(this);

            var value     = $checkBox.parent().parent().attr('data-id');
            var selection = self.getSelectionFromInput();

            if ($checkBox.prop("checked")) {
                selection.push(value);
            } else {
                var index = selection.indexOf(value);

                if (index > -1) {
                    selection.splice(index, 1);
                }
            }

            self.getInputField().val(JSON.stringify(selection));
        });
    },

    onRowClick: function ($row) {
        var $checkBox = $row.find('.select input');
        $checkBox.prop("checked", !$checkBox.prop("checked"));
        $checkBox.change();
    },

    onRowDblClick: function ($row) {
        // do nothing
    },

    selectCheckBoxes: function () {
        var self      = this;
        var selection = this.getSelectionFromInput();

        $.each(selection, function (index, value) {
            self.getDataTable().find('.table tr[data-id=' + value + ']').each(function () {
                $(this).find('.select input').prop("checked", true);
            })
        });
    }
});
