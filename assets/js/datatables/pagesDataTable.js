var PagesDataTable = DataTable.extend({
    actionPath: '/cms/datatable/pages/',
    closedPageIdsCacheKey: "kikcms.closedPageIds",

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
        this.initRowsCollapse();
        this.updateEvenOdd();

        $('.action.preview').click(function () {
            var pageLanguageId = $(this).parent().attr('data-plid');
            window.open('/cms/preview/' + pageLanguageId);
        });
    },

    /**
     *
     * @param $row
     */
    initRowCollapse: function ($row) {
        var self         = this;
        var level        = parseInt($row.attr('data-level'));
        var nextRowLevel = parseInt($row.next().attr('data-level'));

        if (nextRowLevel == level + 1) {
            $row.addClass('hasChildren');
        }

        $row.find('.arrow').click(function (e) {
            self.onCollapseArrowClick(e, $(this), $row, level);
        }).on('dblclick', function (e) {
            e.stopPropagation();
        });
    },

    /**
     * Handles logic for collapsing pages
     */
    initRowsCollapse: function () {
        var self  = this;
        var $rows = this.getRows();

        $rows.each(function () {
            self.initRowCollapse($(this));
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

    /**
     * @param closeWindow
     */
    actionSave: function (closeWindow) {
        var pageName = this.getForm().find('input[name="pageLanguage*:name"]').val();
        var type     = this.getForm().find('input[name=type]').val();

        if (type != 'link') {
            this.getForm().find('input[name="pageLanguage*:url"]').each(function () {
                if (!$(this).val()) {
                    $(this).val(KikCMS.toSlug(pageName));
                }
            });
        }

        this.$.actionSave.call(this, closeWindow);
    },

    /**
     * Overrides default DataTable getFilters to add the template parameters
     * @returns {*}
     */
    getFilters: function () {
        var filters = this.$.getFilters.call(this);

        this.getTemplateField().each(function () {
            filters.template = $(this).val();
        });

        this.getWindow().find('input[name=type]').each(function () {
            filters.pageType = $(this).val();
        });

        return filters;
    },

    /**
     * @return {*}
     */
    getRows: function () {
        return this.$table.find('tbody tr');
    },

    /**
     * @param e
     * @param $arrow
     * @param $row
     * @param level
     */
    onCollapseArrowClick: function(e, $arrow, $row, level) {
        e.stopPropagation();

        $arrow.toggleClass('closed');

        var skipLevel = null;

        $row.nextAll().each(function () {
            var $nextRow     = $(this);
            var nextRowLevel = parseInt($nextRow.attr('data-level'));

            if (skipLevel !== null && nextRowLevel >= skipLevel) {
                return true;
            }

            skipLevel = null;

            // if this row is a parent which is closed, mark its children to skip
            if (!$arrow.hasClass('closed') && $nextRow.find('.arrow').hasClass('closed')) {
                skipLevel = nextRowLevel + 1;
            }

            if (nextRowLevel > level) {
                $nextRow.toggleClass('collapsed', $arrow.hasClass('closed'));
            } else {
                return false;
            }
        });

        this.updateEvenOdd();
        this.updateClosedIdsSetting();
    },

    /**
     * @param pageId
     * @param targetPageId
     * @param position
     */
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
        return this.getWindow().find('#template');
    },

    /**
     * Update the user's settings with closedIds
     */
    updateClosedIdsSetting: function () {
        var closedIds = [];

        this.$table.find('tbody tr').each(function () {
            if ($(this).find('.arrow.closed').length) {
                closedIds.push($(this).attr('data-id'));
            }
        });

        KikCMS.action('/cms/user-settings/update-closed-page-ids', {
            ids: closedIds,
            className: this.renderableClass
        }, function (response) {
            if (!response.success) {
                console.error('Failed storing closedPageIds');
            }
        });
    },

    /**
     * Set even and odd by visibility
     */
    updateEvenOdd: function () {
        var $rows = this.getRows();

        $rows.removeClass('even').removeClass('odd');
        $rows.filter(':visible:even').addClass('even');
        $rows.filter(':visible:odd').addClass('odd');
    }
});
