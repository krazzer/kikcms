var PagesDataTable = DataTable.extend({
    actionPath: '/cms/datatable/pages/',

    init: function () {
        this.$.init.call(this);
        this.initPageTypeMenu();
    },

    initWindow: function () {
        this.$.initWindow.call(this);
        this.onChange(this.getTemplateField(), this.actionReloadWindow.bind(this));
    },

    initPageTypeMenu: function () {
        var self = this;

        this.getDataTable().find('.pageTypes ul li a').click(function () {
            self.actionAdd({'pageType': $(this).attr('data-type')});
        });
    },

    initTable: function () {
        this.$.initTable.call(this);
        this.initTreeSortControl();

        $('.action.preview').click(function () {
            var pageLanguageId = $(this).parent().attr('data-plid');
            window.open('/cms/preview/' + pageLanguageId);
        });
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

    actionSave: function (closeWindow) {
        var pageName = this.getForm().find('input[name=name]').val();

        this.getForm().find('input[name=url]').each(function () {
            if (!$(this).val()) {
                $(this).val(KikCMS.toSlug(pageName));
            }
        });

        this.$.actionSave.call(this, closeWindow);
    },

    /**
     * Overrides default DataTable getFilters to add the templateId parameters
     * @returns {*}
     */
    getFilters: function () {
        var filters = this.$.getFilters.call(this);

        this.getTemplateField().each(function () {
            filters.templateId = $(this).val();
        });

        this.getWindow().find('input[name=type]').each(function () {
            filters.pageType = $(this).val();
        });

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
    },

    /**
     * Get the field where you can choose a template
     * @returns {*|{}}
     */
    getTemplateField: function () {
        return this.getWindow().find('#template_id');
    }
});
