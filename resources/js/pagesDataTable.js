var PagesDataTable = DataTable.extend({
    templateId: null,

    /**
     * Override default init to initialize additional behaviour
     */
    init: function () {
        this.$.init.call(this);
        this.initTemplateSwitch();
    },

    /**
     * Override default init to initialize additional behaviour
     */
    initTable: function () {
        this.$.initTable.call(this);
        this.initTreeSortControl();
    },

    /**
     * Initialize the dragging of pages for ordering
     */
    initTreeSortControl: function () {
        var treeSortControl        = new TreeSortControl();
        treeSortControl.$dataTable = this.getDataTable();
        treeSortControl.onDrop     = this.onPageDrop.bind(this);
        treeSortControl.init();
    },

    /**
     * Reload the datatable window if the template changes
     */
    initTemplateSwitch: function () {
        var self = this;

        $('body').on('change', '#template_id', function () {
            var $templateField = $(this);
            self.templateId    = $templateField.val();

            var editId = $templateField.closest('form').find('#editId').val();

            if (editId) {
                self.actionEdit(editId);
            } else {
                self.actionAdd();
            }

            self.templateId = null;
        });
    },

    /**
     * Overrides default DataTable getFilters to add the templateId parameters
     * @returns {*}
     */
    getFilters: function () {
        var filters = this.$.getFilters.call(this);

        if (this.templateId) {
            filters.templateId = this.templateId;
        }

        return filters;
    },

    onPageDrop: function (pageId, targetPageId, position) {
        var self       = this;
        var parameters = this.getFilters();

        parameters.pageId       = pageId;
        parameters.targetPageId = targetPageId;
        parameters.position     = position;

        parameters = this.addActionParameters(parameters);

        KikCMS.action('/cms/datatable/pages/tree-order', parameters, function (result) {
            self.setTableContent(result.table);
        });
    }
});
