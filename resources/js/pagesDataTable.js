//todo: split up (and rename) initDraggables function, possibly in it's own class

var PagesDataTable = DataTable.extend({
    isDragging: false,
    hoveringNode: false,
    draggedObject: null,
    startX: 0,
    startY: 0,

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
        this.initDraggables();
    },

    /**
     * Initialize the dragging of pages for ordering
     */
    initDraggables: function () {
        var self       = this;
        var $dataTable = this.getDataTable();
        var $body      = $('body');
        var $window    = $(window);

        var $pagesObjects     = $dataTable.find('.pageObject');
        var $draggableObjects = $pagesObjects.find('.name');

        $draggableObjects.mousedown(function (e) {
            // left mouse button only
            if (e.which !== 1) {
                return;
            }

            self.draggedObject = $(this);

            self.startX = e.clientX;
            self.startY = e.clientY;
        });

        $draggableObjects.hover(function () {
            if (self.isDragging) {
                $(this).addClass('dragHover');
                $(this).parent().parent().removeClass('dragHover');
                self.hoveringNode = true;
            }
        }, function () {
            if (self.isDragging) {
                $(this).removeClass('dragHover');
                $(this).parent().parent().addClass('dragHover');
                self.hoveringNode = false;
            }
        });

        $pagesObjects.hover(function () {
            if (self.isDragging) {
                $(this).addClass('dragHover');

            }
        }, function () {
            if (self.isDragging) {
                $(this).removeClass('dragHover');
            }
        });

        $pagesObjects.mousemove(function (e) {
            var $pageObject = $(this);

            if (!$pageObject.hasClass('dragHover')) {
                return;
            }

            var height = $pageObject.outerHeight();

            if (e.clientY > $pageObject.offset().top + (height / 2)) {
                $pageObject.addClass('bottom');
                $pageObject.removeClass('top');
            } else {
                $pageObject.addClass('top');
                $pageObject.removeClass('bottom');
            }
        });

        $window.mousemove(function (e) {
            if (self.isDragging) {
                var $dragObject = self.getDragObject();

                $dragObject.css('left', e.clientX - (self.startX - self.draggedObject.offset().left));
                $dragObject.css('top', e.clientY - (self.startY - self.draggedObject.offset().top));

                if (document.selection) {
                    document.selection.empty()
                } else {
                    window.getSelection().removeAllRanges()
                }

            } else if (self.draggedObject) {
                var dragThreshold = 10;

                var outOfDragThresholdX = e.clientX > self.startX + dragThreshold || e.clientX < self.startX - dragThreshold;
                var outOfDragThresholdY = e.clientY > self.startY + dragThreshold || e.clientY < self.startY - dragThreshold;

                if (outOfDragThresholdX || outOfDragThresholdY) {
                    self.isDragging = true;
                    self.draggedObject.addClass('dragged');
                    $body.addClass('noSelect');
                }
            }
        });

        var stopDrag = function () {
            self.isDragging = false;

            if (self.draggedObject) {
                self.getDragObject().remove();
                self.draggedObject.removeClass('dragged');
                self.draggedObject = null;
                $body.removeClass('noSelect');
                $dataTable.find('.pageObject .name').removeClass('dragHover');
                $dataTable.find('.pageObject').removeClass('dragHover');
            }
        };

        $window.mouseup(stopDrag);
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
     * Get the object that will be shown dragging around
     *
     * @returns {*|jQuery|HTMLElement}
     */
    getDragObject: function () {
        var $dragObject = $('.draggedObject');

        if ($dragObject.length) {
            return $dragObject;
        }

        $dragObject = this.draggedObject.clone();
        $dragObject.addClass('draggedObject');

        $('body').append($dragObject);

        return $dragObject;
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
    }
});
