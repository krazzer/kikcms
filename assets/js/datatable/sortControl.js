var SortControl = Class.extend({
    dragHoverClass: 'dragHover',

    isDragging: false,
    hoveringNode: false,
    draggedObject: null,
    timeDragStarted: 0,
    startX: 0,
    startY: 0,

    moveOnlyVertical: true,

    selectedObjectX: 0,
    selectedObjectY: 0,

    $dataTable: null,
    onDrop: null,

    /**
     * Handles dragging pages and hovering pages while dragging
     */
    init: function () {
        var self    = this;
        var $window = $(window);

        var $hoverObjects     = this.getHoverObjects();
        var $draggableObjects = this.getDraggables();

        $draggableObjects.on('selectstart', function (e) {
            e.preventDefault();
        });

        $draggableObjects.mousedown(function (e) {
            // left mouse button only
            if (e.which !== 1) {
                return;
            }

            self.draggedObject = $(this);

            self.selectedObjectX = self.getObjectToBeDragged().offset().left;
            self.selectedObjectY = self.getObjectToBeDragged().offset().top;

            self.startX = e.clientX + $window.scrollLeft();
            self.startY = e.clientY + $window.scrollTop();
        });

        $hoverObjects.hover(function () {
            if (self.isDragging) {
                $(this).addClass(self.dragHoverClass);
            }
        }, function () {
            if (self.isDragging) {
                $(this).removeClass(self.dragHoverClass);
                $(this).removeAttr('data-drop');
            }
        });

        $hoverObjects.mousemove(function (e) {
            if (!self.isDragging) {
                return;
            }

            var $hoverObject = $(this);

            if (!self.isValidDropPosition($hoverObject, e.clientY)) {
                $hoverObject.removeAttr('data-drop');
                return;
            }

            $hoverObject.attr('data-drop', self.getHoverPosition(e.clientY + $window.scrollTop(), $hoverObject));
        });

        $window.mousemove(this.windowMouseMove.bind(this));
        $window.mouseup(this.endDrag.bind(this));
    },

    /**
     * Clone the element that is selected
     *
     * @returns {*}
     */
    cloneSelectedObject: function () {
        var $row      = this.draggedObject.parent().parent().parent();
        var $rowClone = $("<table class='draggedObject rowClone'></table>").append($row.clone());

        $rowClone.css('width', $row.innerWidth());

        $row.find('td').each(function (index) {
            $rowClone.find('td').eq(index).css('width', $(this).innerWidth());
        });

        return $rowClone;
    },

    /**
     * Remove all traces of an item being dragged
     */
    endDrag: function () {
        if (!this.draggedObject) {
            return;
        }

        var $selectedRow = this.getSelectedRow();

        if (this.isDragging) {
            $selectedRow.attr('data-prevent-click', 1);

            setTimeout(function () {
                $selectedRow.removeAttr('data-prevent-click');
            })
        }

        var $hoverObjects = this.getHoverObjects();

        this.isDragging = false;

        this.getDragObject().remove();

        var $hoveringObject = this.$dataTable.find('.' + this.dragHoverClass);
        var isNoUserMistake = (new Date().getTime() - this.timeDragStarted) > 250;

        if ($hoveringObject.length && this.onDrop && isNoUserMistake) {
            var targetId = $hoveringObject.attr('data-id');
            var position = $hoveringObject.attr('data-drop');
            var id = $selectedRow.attr('data-id');

            if (position) {
                this.onDrop(id, targetId, position);
            }
        }

        $selectedRow.removeClass('dragged');
        this.draggedObject = null;

        $hoverObjects.find('.name').removeClass(this.dragHoverClass);
        $hoverObjects.removeClass(this.dragHoverClass);

        $('body').removeClass('noSelect').removeClass('isDragging');
    },

    /**
     * Get the dummy object that will be shown dragging around
     *
     * @returns {*|jQuery|HTMLElement}
     */
    getDragObject: function () {
        var $dragObject = $('.draggedObject');

        if ($dragObject.length) {
            return $dragObject;
        }

        $dragObject = this.cloneSelectedObject();
        $dragObject.addClass('draggedObject');

        $('body').append($dragObject);

        return $dragObject;
    },

    getHoverPosition: function (clientY, $hoverObject) {
        var height = $hoverObject.outerHeight();
        return clientY > $hoverObject.offset().top + (height / 2) ? 'after' : 'before';
    },

    /**
     * Get the object that is to be cloned dragging around
     */
    getObjectToBeDragged: function () {
        return this.getSelectedRow();
    },

    /**
     * Get the row object for the selected element
     */
    getSelectedRow: function () {
        return this.draggedObject.parent().parent().parent();
    },

    /**
     * Get the elements that contain a draggable element
     *
     * @returns {*|{}}
     */
    getDraggables: function () {
        return this.$dataTable.find('tbody tr td .actions .sort');
    },

    /**
     * Get the elements that contain a draggable element
     *
     * @returns {*|{}}
     */
    getHoverObjects: function () {
        return this.$dataTable.find('tbody tr');
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
     * Determine if the given $hoverObject can be dropped upon
     *
     * @param $hoverObject
     * @param clientY
     * @returns {boolean}
     */
    isValidDropPosition: function ($hoverObject, clientY) {
        var position = this.getHoverPosition(clientY, $hoverObject);

        if (position == 'after' && $hoverObject.next()[0] == this.getSelectedRow()[0]) {
            return false;
        }

        if (position == 'before' && $hoverObject.prev()[0] == this.getSelectedRow()[0]) {
            return false;
        }

        return $hoverObject[0] != this.getSelectedRow()[0];
    },

    /**
     * Handles visually moving around the dragged object
     *
     * @param e
     */
    windowMouseMove: function (e) {
        var $window = $(window);

        var clientX = e.clientX + $window.scrollLeft();
        var clientY = e.clientY + $window.scrollTop();

        if (this.isDragging) {
            var $dragObject = this.getDragObject();

            var left = this.moveOnlyVertical ? this.selectedObjectX : clientX - (this.startX - this.selectedObjectX);

            $dragObject.css('left', left);
            $dragObject.css('top', clientY - (this.startY - this.selectedObjectY));
        } else if (this.draggedObject) {
            var dragThreshold = 5;

            var outOfDragThresholdX = clientX > this.startX + dragThreshold || clientX < this.startX - dragThreshold;
            var outOfDragThresholdY = clientY > this.startY + dragThreshold || clientY < this.startY - dragThreshold;

            if (outOfDragThresholdX || outOfDragThresholdY) {
                this.isDragging      = true;
                this.timeDragStarted = new Date().getTime();

                this.getSelectedRow().addClass('dragged');

                $('body').addClass('noSelect').addClass('isDragging');
            }
        }
    }
});