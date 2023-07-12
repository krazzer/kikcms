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
        var selection = this.isNumeric() ? {} : [];

        if (this.getInputField().val()) {
            selection = JSON.parse(this.getInputField().val());
        }

        if(Array.isArray(selection) && this.isNumeric()){
            selection = {};
        }

        return selection;
    },

    /**
     * @returns {*}
     */
    getAmountInputFields: function (){
        return this.getDataTable().find('td.amount input');
    },

    initTable: function () {
        this.$.initTable.call(this);

        this.fill();

        this.initCheckBoxes();
        this.initAmountBoxes();
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

    initAmountBoxes: function () {
        var self = this;

        this.getAmountInputFields().change(function () {
            var $amountInput = $(this);

            var id        = $amountInput.parent().parent().attr('data-id');
            var selection = self.getSelectionFromInput();
            var amount    = $amountInput.val();

            if (amount) {
                selection[id] = amount;
            } else {
                delete selection[id];
            }

            self.getInputField().val(JSON.stringify(selection));
        });
    },

    isNumeric: function (){
        return !! this.getDataTable().find('td.amount').length;
    },

    onRowClick: function ($row) {
        var $checkBox = $row.find('.select input');
        $checkBox.prop("checked", ! $checkBox.prop("checked"));
        $checkBox.change();
    },

    onRowDblClick: function ($row) {
        // do nothing
    },

    fill: function () {
        var self      = this;
        var selection = this.getSelectionFromInput();

        $.each(selection, function (index, value) {
            self.getDataTable().find('.table tr[data-id=' + value + ']').each(function () {
                $(this).find('.select input').prop("checked", true);

            });

            self.getDataTable().find('.table tr[data-id=' + index + ']').each(function () {
                $(this).find('.amount input').val(value);

            });
        });
    },

    preventTableDoubleClickTextSelect: function (){
        // do nothing
    }
});
