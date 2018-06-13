var PagesFlatDataTable = PagesDataTable.extend({
    /**
     * Overrides default DataTable getFilters to add the template parameters
     * @returns {*}
     */
    getFilters: function () {
        var filters = this.$.getFilters.call(this);

        filters.template = this.template;

        return filters;
    }
});