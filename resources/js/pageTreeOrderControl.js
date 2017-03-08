var PageTreeOrderControl = Class.extend({
    dragHoverClass: 'dragHover',

    isDragging: false,
    hoveringNode: false,
    draggedObject: null,
    startX: 0,
    startY: 0,

    $dataTable: null,

    /**
     * Handles dragging pages and hovering pages while dragging
     */
    init: function () {
        var self    = this;
        var $window = $(window);

        var $pagesObjects     = this.getPagesObjects();
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
                $(this).addClass(self.dragHoverClass);
                $(this).parent().parent().removeClass(self.dragHoverClass);
                self.hoveringNode = true;
            }
        }, function () {
            if (self.isDragging) {
                $(this).removeClass(self.dragHoverClass);
                $(this).parent().parent().addClass(self.dragHoverClass);
                self.hoveringNode = false;
            }
        });

        $pagesObjects.hover(function () {
            if (self.isDragging) {
                $(this).addClass(self.dragHoverClass);

            }
        }, function () {
            if (self.isDragging) {
                $(this).removeClass(self.dragHoverClass);
            }
        });

        $pagesObjects.mousemove(function (e) {
            var $pageObject = $(this);

            if (!$pageObject.hasClass(self.dragHoverClass)) {
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

        $window.mousemove(this.windowMouseMove.bind(this));
        $window.mouseup(this.endDrag.bind(this));
    },

    /**
     * Remove all traces of an item being dragged
     */
    endDrag: function () {
        if (!this.draggedObject) {
            return;
        }

        var $pagesObjects = this.getPagesObjects();

        this.isDragging = false;

        this.getDragObject().remove();

        this.draggedObject.removeClass('dragged');
        this.draggedObject = null;

        $pagesObjects.find('.name').removeClass(this.dragHoverClass);
        $pagesObjects.removeClass(this.dragHoverClass);

        $('body').removeClass('noSelect');
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
     * Get the elements that contain a draggable element
     *
     * @returns {*|{}}
     */
    getPagesObjects: function () {
        return this.$dataTable.find('.pageObject');
    },

    windowMouseMove: function (e) {
        if (this.isDragging) {
            var $dragObject = this.getDragObject();

            $dragObject.css('left', e.clientX - (this.startX - this.draggedObject.offset().left));
            $dragObject.css('top', e.clientY - (this.startY - this.draggedObject.offset().top));
        } else if (this.draggedObject) {
            var dragThreshold = 10;

            var outOfDragThresholdX = e.clientX > this.startX + dragThreshold || e.clientX < this.startX - dragThreshold;
            var outOfDragThresholdY = e.clientY > this.startY + dragThreshold || e.clientY < this.startY - dragThreshold;

            if (outOfDragThresholdX || outOfDragThresholdY) {
                this.isDragging = true;
                this.draggedObject.addClass('dragged');

                $('body').addClass('noSelect');
            }
        }
    }
});