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
        if (this.hoveringNode) {
            return 'into';
        }

        return this.$.getHoverPosition.call(this, clientY, $hoverObject);
    },

    getSelectedRow: function () {
        return this.draggedObject.parent().parent();
    },

    isValidDropPosition: function ($hoverObject, clientY) {
        return !$hoverObject.hasClass('dragged') && clientY;
    }
});