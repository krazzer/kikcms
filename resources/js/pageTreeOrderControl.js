var PageTreeOrderControl = Class.extend({
    dragHoverClass: 'dragHover',

    isDragging: false,
    hoveringNode: false,
    draggedObject: null,
    timeDragStarted: 0,
    startX: 0,
    startY: 0,

    $dataTable: null,
    onDrop: null,

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
            if (self.isDraggingAndNotCurrent($(this))) {
                $(this).parent().parent().attr('data-drop', 'into');
                self.hoveringNode = true;
            }
        }, function () {
            if (self.isDraggingAndNotCurrent($(this))) {
                $(this).parent().parent().removeAttr('data-drop');
                self.hoveringNode = false;
            }
        });

        $pagesObjects.hover(function () {
            if (self.isDraggingAndNotCurrent($(this).find('.name'))) {
                $(this).addClass(self.dragHoverClass);
            }
        }, function () {
            if (self.isDraggingAndNotCurrent($(this).find('.name'))) {
                $(this).removeClass(self.dragHoverClass);
                $(this).removeAttr('data-drop');
            }
        });

        $pagesObjects.mousemove(function (e) {
            if (!self.isDragging) {
                return;
            }

            var $pageObject = $(this);

            if ($pageObject.attr('data-drop') == 'into') {
                return;
            }

            var height   = $pageObject.outerHeight();
            var position = e.clientY > $pageObject.offset().top + (height / 2) ? 'after' : 'before';

            $pageObject.attr('data-drop', position);
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

        var $hoveringObject = this.$dataTable.find('.' + this.dragHoverClass);
        var isNoUserMistake = (new Date().getTime() - this.timeDragStarted) > 250;

        if ($hoveringObject.length && this.onDrop && isNoUserMistake) {
            var targetId = $hoveringObject.attr('data-id');
            var position = $hoveringObject.attr('data-drop');
            var id       = this.draggedObject.parent().parent().attr('data-id');

            this.onDrop(id, targetId, position);
        }

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

    /**
     * Check if we are dragging an element and it's not the one given
     *
     * @param $element
     * @returns {boolean}
     */
    isDraggingAndNotCurrent: function ($element) {
        if (!this.isDragging) {
            return false;
        }

        return !$element.hasClass('dragged');
    },

    /**
     * Handles visually moving around the dragged object
     *
     * @param e
     */
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
                this.isDragging      = true;
                this.timeDragStarted = new Date().getTime();

                this.draggedObject.addClass('dragged');

                $('body').addClass('noSelect');
            }
        }
    }
});