var FinderPermission = Class.extend({
    finder: null,

    /**
     * @param finder
     */
    construct: function (finder) {
        this.finder = finder;
    },

    /**
     * Initialize the permission Modal
     */
    init: function () {
        var self = this;

        var $modal = this.getModal();
        var $form  = this.getForm();

        $modal.find('.save').click(this.onSaveButtonClick.bind(this));

        $form.on('change', '.check input', function () {
            self.updateDependentCheckboxes($(this));
        });

        $form.on('change', 'select', function () {
            self.onUserSelect($(this));
        });
    },

    /**
     * @returns jQuery
     */
    getForm: function () {
        return this.getModal().find('form');
    },

    /**
     * Returns the Modal object
     * @returns jQuery
     */
    getModal: function () {
        return this.finder.getFinder().find('.permissionModal');
    },

    /**
     * Returns the Modal object
     * @returns jQuery
     */
    getMessageContainer: function () {
        return this.getModal().find('.messages');
    },

    /**
     * Open the modal window to manage file permissions
     */
    openModal: function () {
        var self = this;

        KikCMS.action('/cms/finder/permission/get', {fileIds: this.finder.getSelectedFileIds()}, function (response) {
            self.updateModal(response);
            self.getModal().modal();
        });
    },

    /**
     * Update other checkboxes, dependent upon the given $checkbox
     * @param $checkbox
     */
    updateDependentCheckboxes: function ($checkbox) {
        var right = $checkbox.attr('data-right');

        var $nextCheckbox = $checkbox.parent().parent().next().find('input');
        var $prevCheckbox = $checkbox.parent().parent().prev().find('input');

        if (right == 'write' && $checkbox.prop('checked')) {
            $prevCheckbox.prop('indeterminate', false);
            $prevCheckbox.prop('checked', true);
        }

        if (right == 'write' && $checkbox.prop('indeterminate')) {
            $prevCheckbox.prop('indeterminate', true);
        }

        if (right == 'read' && !$checkbox.prop('checked')) {
            $nextCheckbox.prop('checked', false);
            $nextCheckbox.prop('indeterminate', false);
        }
    },

    /**
     * Update the modal by given response
     *
     * @param response
     */
    updateModal: function (response) {
        var $modal = this.getModal();
        var $files = this.finder.getSelectedFiles();

        $modal.find('.modal-title .file').html(response.title);
        $modal.find('.messages .alert').hide();

        $modal.find('input').prop('indeterminate', false);
        $modal.find('input').prop('checked', false);

        $modal.find('.sub-files-checkbox').toggle($files.hasClass('folder'));

        this.resetUserValues();

        var $lastRow = $modal.find('table tr:last');

        $.each(response.table, function (key, permission) {
            if (isNumeric(key)) {
                var $row = $lastRow.clone();

                $row.find('select').val(key);
                $row.find('input').removeAttr('disabled');
                $row.find('input:first').attr('name', 'permission[' + key + '][read]').attr('data-right', 'read');
                $row.find('input:last').attr('name', 'permission[' + key + '][write]').attr('data-right', 'write');

                $lastRow.before($row);
            }

            $.each(['read', 'write'], function (index, type) {
                var value     = permission[type];
                var $checkbox = $('input[name="permission[' + key + '][' + type + ']"]');

                $checkbox.removeAttr('disabled');

                switch (value) {
                    case 2:
                        $checkbox.prop("indeterminate", true).trigger('change');
                        break;
                    case 1:
                        $checkbox.prop("checked", true).trigger('change');
                        break;
                }

                if(permission.disabled){
                    $checkbox.attr('disabled', true);
                }
            });
        });
    },

    /**
     * What happens when a user clicks the save button
     */
    onSaveButtonClick: function () {
        var hasIntermediate   = false;
        var $messageContainer = this.getMessageContainer();

        var $allMessages = $messageContainer.find('.alert');
        var $warning     = $messageContainer.find('.warning');
        var $success     = $messageContainer.find('.success');
        var $error       = $messageContainer.find('.error');

        this.getForm().find('input').each(function () {
            if ($(this).prop('indeterminate')) {
                hasIntermediate = true;
            }
        });

        if (hasIntermediate) {
            $allMessages.hide();
            $warning.fadeIn();
            return;
        }

        var data = this.getForm().serializeObject();

        data.fileIds = this.finder.getSelectedFileIds();

        KikCMS.action('/cms/finder/permission/update', data, function (response) {
            $allMessages.hide();

            if (response.success == true) {
                $success.fadeIn();
            } else {
                $error.fadeIn();
            }
        });
    },

    /**
     * When a user select field changes
     * @param $select
     */
    onUserSelect: function ($select) {
        var val  = $select.val();
        var $row = $select.parent().parent();

        if (!val) {
            if ($row.next().length) {
                $row.remove();
            }

            return;
        }

        // add a new row if there isn't one already
        if (!$row.next().length) {
            var $newRow = $row.clone();
            this.getForm().find('table').append($newRow);
        }

        $row.find('input').removeAttr('disabled');
        $row.find('input:first').attr('name', 'permission[' + val + '][read]').attr('data-right', 'read');
        $row.find('input:last').attr('name', 'permission[' + val + '][write]').attr('data-right', 'write');
    },

    /**
     * Resets the user fields
     */
    resetUserValues: function () {
        var $modal = this.getModal();

        $modal.find('select').each(function () {
            var $select = $(this);

            if ($select.val() || $modal.find('select').length > 1) {
                $select.parent().parent().remove();
            }
        });
    }
});