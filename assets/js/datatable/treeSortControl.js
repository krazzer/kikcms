var TreeSortControl = SortControl.extend({
    moveOnlyVertical: false,
    hoveringNode: false,

    init: function () {
        this.$.init.call(this);

        var self = this;

        this.getDraggables().hover(function () {
            self.hoveringNode = true;
        }, function () {
            self.hoveringNode = false;
        });
    },

    cloneSelectedObject: function () {
        return this.draggedObject.clone()
    },

    getDraggables: function () {
        return this.getHoverObjects().find('.name');
    },

    getObjectToBeDragged: function () {
        return this.draggedObject;
    },

    getHoverObjects: function () {
        return this.$dataTable.find('.pageObject');
    },

    getHoverPosition: function (clientY, $hoverObject) {
        if (this.hoveringNode && this.mayDropInto($hoverObject)) {
            return 'into';
        }

        return this.$.getHoverPosition.call(this, clientY, $hoverObject);
    },

    getParentRow: function () {
        var $selectedRow = this.getSelectedRow();
        var level        = $selectedRow.attr('data-level');

        return $selectedRow.prevAll('.level' + (level - 1) + ':first');
    },

    getSelectedRow: function () {
        return this.draggedObject.parent().parent();
    },

    isValidDropPosition: function ($hoverObject, clientY) {
        return !$hoverObject.hasClass('dragged') && clientY;
    },

    mayDropInto: function ($hoverObject) {
        var level      = parseInt($hoverObject.attr('data-level'));
        var $parentRow = this.getParentRow();

        // no use placing a page into it's own parent
        if ($parentRow && $parentRow.attr('data-id') == $hoverObject.attr('data-id')) {
            return false;
        }

        if ($hoverObject.hasClass('detached')) {
            return false;
        }

        if (level > 0) {
            var maxLevel = $hoverObject.prevAll('.level0:first').attr('data-max-level');

            if (level >= maxLevel) {
                return false;
            }
        }

        return true;
    }
});