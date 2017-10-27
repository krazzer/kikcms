var TranslationsDataTable = DataTable.extend({
    initWindow: function () {
        this.$.initWindow.call(this);
        this.initKeyField();
    },

    initKeyField: function () {
        var self      = this;
        var $keyField = this.getForm().find('select[name=key]');

        this.updateTranslationFields($keyField.val());

        $keyField.change(function () {
            self.updateTranslationFields($(this).val());
        });
    },

    updateTranslationFields: function (key) {
        var self = this;

        KikCMS.action('/cms/getTranslationsForKey', {key: key}, function (result) {
            if (!result) {
                return;
            }

            $.each(result, function (key, value) {
                self.getForm().find('textarea[data-language-code=' + key + ']').val(value);
            });
        });
    }
});