/**
 * This class controls the restoring of filled in datatable form data that was lost by closing a tab suddenly
 */
var DataTableRestore = Class.extend({
    dataTable: null,
    interval: null,
    previousContent: null,

    /**
     * @param dataTable
     */
    construct: function (dataTable) {
        this.dataTable = dataTable;
    },

    getPreviousContent: function () {
        return this.previousContent;
    },

    /**
     * @return {string}
     */
    getStorageKey: function () {
        return 'kikcms.dt.' + this.dataTable.getClassWithoutSlashes() + '.new';
    },

    /**
     * Start storing the datatable windows' content every 10s
     */
    startPolling: function () {
        var self       = this;
        var storageKey = this.getStorageKey();

        var previousContent = localStorage.getItem(storageKey);

        if (previousContent) {
            this.previousContent = previousContent;
        } else {
            this.previousContent = null;
        }

        this.interval = setInterval(function () {
            if (!self.dataTable.windowIsActive()) {
                return;
            }

            localStorage.setItem(storageKey, JSON.stringify(self.dataTable.getFormGroups().serializeObject()));
        }, 10000);
    },

    /**
     * Stop storing the datatable windows' content and remove content
     */
    stopPolling: function () {
        localStorage.removeItem(this.getStorageKey());
        clearInterval(this.interval);
    }
});