/**
 * This class controls the restoring of filled in datatable form data that was lost by closing a tab suddenly
 */
var DataTableRestore = Class.extend({
    dataTable: null,
    interval: null,
    storageIndex: null,

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
            this.storageIndex = Object.keys(content).length;
        } else {
            this.storageIndex = 0;
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
    remove: function () {
        var content = this.getStorageContent();

        delete content[this.storageIndex];

        if (Object.keys(content).length == 0) {
            localStorage.removeItem(this.getStorageKey());
        } else {
            localStorage.setItem(this.getStorageKey(), JSON.stringify(content));
        }
    },

    /**
     * Display the button to restore data
     */
    showButton: function () {
        var $restoreButton = this.dataTable.getWindow().find('.footer > .restore');
        var content        = this.getStorageContent();
        var self           = this;

        $restoreButton.show();

        $.each(content, function (key, restoreObject) {
            var date = self.getFormattedDateByRestoreObject(restoreObject);
            $restoreButton.find('ul').append('<li><a data-id="' + key + '">' + date + '</a></li>');
        });

        $restoreButton.find('ul li a').click(function () {
            var restoreIndex  = $(this).data('id');
            var restoreObject = content[restoreIndex];
            var date          = self.getFormattedDateByRestoreObject(restoreObject);

            if (confirm(KikCMS.tl('dataTable.restoreConfirm', {date: date}))) {
                $.each(restoreObject.content, function (key, value) {
                    self.dataTable.getForm().find('[name=' + key + ']').val(value);
                });
            }
        })
    },

    /**
     * Start storing the datatable windows' content every 10s
     */
    startPolling: function () {
        var self    = this;
        var content = this.getContent();

        this.determineStorageIndex();

        if (this.storageIndex > 0) {
            this.showButton();
        }

        this.interval = setInterval(function () {
            if (!self.dataTable.windowIsActive()) {
                return;
            }

            if (JSON.stringify(content) === JSON.stringify(self.getContent())) {
                self.remove();
                return;
            }

            self.store();
        }, 10000);
    },

    /**
     * Stop storing the datatable windows' content and remove content
     */
    stopPolling: function () {
        this.remove();
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