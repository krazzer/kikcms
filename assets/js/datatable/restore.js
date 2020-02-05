/**
 * This class controls the restoring of filled in datatable form data that was lost by closing a tab suddenly
 */
var DataTableRestore = Class.extend({
    dataTable: null,
    interval: null,
    storageIndex: null,
    pollInterval: 10000,

    /**
     * @param dataTable
     */
    construct: function (dataTable) {
        this.dataTable = dataTable;
    },

    /**
     * Set storageIndex based on existing keys
     */
    determineStorageIndex: function () {
        var content = JSON.parse(localStorage.getItem(this.getStorageKey()));

        if (content) {
            this.storageIndex = Math.max(Object.keys(content)) + 1;
        } else {
            this.storageIndex = 1;
        }
    },

    /**
     * @return {string}
     */
    getContent: function () {
        return this.dataTable.getFormGroups().serializeObject();
    },

    /**
     * @param {object} restoreObject
     * @return {string}
     */
    getFormattedDateByRestoreObject: function (restoreObject) {
        var dateString = restoreObject.date;
        return moment(dateString).format('dddd D MMMM YYYY, HH:mm');
    },

    /**
     * @return {*}
     */
    getRestoreButton: function () {
        return this.dataTable.getWindow().find('.footer > .restore');
    },

    /**
     * @return {object}
     */
    getStorageContent: function () {
        var content = localStorage.getItem(this.getStorageKey());

        if (content) {
            return JSON.parse(content);
        }

        return {};
    },

    /**
     * @return {string}
     */
    getStorageKey: function () {
        var editId = this.dataTable.getFormEditId();
        return 'kikcms.dt.' + this.dataTable.getClassWithoutSlashes() + '.' + (editId ? editId : 'new');
    },

    /**
     * Remove from local storage
     */
    remove: function (index) {
        var content = this.getStorageContent();

        delete content[index];

        if (Object.keys(content).length == 0) {
            localStorage.removeItem(this.getStorageKey());
        } else {
            localStorage.setItem(this.getStorageKey(), JSON.stringify(content));
        }
    },

    /**
     * @param restoreIndex
     */
    restore: function (restoreIndex) {
        var self          = this;
        var restoreObject = this.getStorageContent()[restoreIndex];
        var date          = this.getFormattedDateByRestoreObject(restoreObject);

        if (confirm(KikCMS.tl('dataTable.restoreConfirm', {date: date}))) {
            $.each(restoreObject.content, function (key, value) {
                self.restoreField(key, value);
            });
        }

        this.remove(restoreIndex);
        this.getRestoreButton().find('[data-id=' + restoreIndex + ']').parent().remove();

        if (!this.getRestoreButton().find('li').length) {
            this.getRestoreButton().hide();
        }
    },

    /**
     * @param key
     * @param value
     */
    restoreField: function (key, value) {
        var $field        = this.dataTable.getForm().find('[name=' + key + ']');
        var previousValue = $field.val();

        $field.val(value);

        // restore a DataTable field
        if (previousValue in KikCMS.renderables) {
            $('#' + previousValue).attr('id', value);
            KikCMS.renderables[previousValue].renderableInstance = value;
            KikCMS.renderables[previousValue].actionPage(1);
        }

        // restore file field
        if($field.parent().hasClass('type-file')) {
            var webForm = KikCMS.renderables[this.dataTable.getForm().parent().data('instance')];
            webForm.actionPickFile($field.parent(), value);
        }
    },

    /**
     * Display the button to restore data
     */
    showButton: function () {
        var $restoreButton = this.getRestoreButton();
        var content        = this.getStorageContent();
        var self           = this;

        $restoreButton.show();

        $.each(content, function (key, restoreObject) {
            var date = self.getFormattedDateByRestoreObject(restoreObject);
            $restoreButton.find('ul').append('<li><a data-id="' + key + '">' + date + '</a></li>');
        });

        $restoreButton.find('ul li a').click(function () {
            self.restore($(this).data('id'));
        });
    },

    /**
     * Start storing the datatable windows' content every 10s
     */
    startPolling: function () {
        var self          = this;
        var content       = this.getContent();
        var storedContent = this.getStorageContent();

        this.determineStorageIndex();

        if (Object.keys(storedContent).length !== 0) {
            this.showButton();
        }

        this.interval = setInterval(function () {
            if (!self.dataTable.windowIsActive()) {
                return;
            }

            if (JSON.stringify(content) === JSON.stringify(self.getContent())) {
                self.remove(self.storageIndex);
                return;
            }

            self.store();
        }, this.pollInterval);
    },

    /**
     * Stop storing the datatable windows' content and remove content
     */
    stopPolling: function () {
        this.remove(this.storageIndex);
        clearInterval(this.interval);
    },

    /**
     * Store in local storage
     */
    store: function () {
        var content = this.getStorageContent();

        content[this.storageIndex] = {
            date: new Date(),
            content: this.getContent()
        };

        localStorage.setItem(this.getStorageKey(), JSON.stringify(content));
    }
});