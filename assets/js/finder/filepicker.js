var FilePicker = Class.extend({
    instance: null,
    $element: null,
    onPickFile: null,

    /**
     * @param instance
     * @param $element
     * @param onPickFile
     */
    construct: function (instance, $element, onPickFile) {
        this.instance   = instance;
        this.onPickFile = onPickFile;
        this.$element   = $element;
    },

    /**
     * Close the window
     */
    close: function () {
        KikCMS.windowManager.closeWindow(this.getWindow());
    },

    /**
     * Initialize the file picker window
     */
    initWindow: function (finderContent) {
        var self    = this;
        var $window = this.getWindow();

        var $windowContent = $window.find('.windowContent');

        $windowContent.html(finderContent);
        $windowContent.unbind('pick');
        $windowContent.unbind('selectionChange');

        $(window).unbind('keypress.' + this.instance);

        var $pickButton = $window.find('.pick-file');

        KikCMS.windowManager.showWindow($window);

        this.initWindowSize();
        $(window).resize(this.initWindowSize.bind(this));

        $windowContent.on("pick", '.file', function () {
            self.pickFile($(this));
        });

        $windowContent.on("selectionChange", '.file', function () {
            if ($windowContent.find('.file.selected:not(.folder)').length >= 1) {
                $pickButton.removeClass('disabled');
            } else {
                $pickButton.addClass('disabled');
            }
        });

        $pickButton.click(function () {
            if (!$pickButton.hasClass('disabled')) {
                self.pickFile($windowContent.find('.file.selected'));
            }
        });

        var keyPressEvent = function (e) {
            if (e.keyCode == keyCode.ESCAPE && $window.is(':visible')) {
                KikCMS.windowManager.closeWindow($window);
            }
        };

        $(window).bind('keypress.' + this.instance, keyPressEvent);
    },

    /**
     * Set file-picker container height
     */
    initWindowSize: function () {
        var $window = this.getWindow();

        var $footer = $window.find('.windowContent > .footer');
        var $header = $window.find('.windowContent > .header');

        var windowHeight = $window.height();
        var headerHeight = $header.outerHeight();
        var footerHeight = $footer.outerHeight();

        $window.find('.files').css('height', windowHeight - headerHeight - footerHeight - 132);
    },

    /**
     * Show the filepicker
     */
    open: function () {
        var self = this;

        var $window = this.getWindow();

        $window.on('click', '.buttons .cancel', function () {
            self.close();
        });

        KikCMS.action('/cms/webform/getFinder', {}, function (result) {
            self.initWindow(result.finder);
        });
    },

    /**
     * @param $file
     */
    pickFile: function($file){
        $file.removeClass('selected');

        this.onPickFile($file);
        this.close();
    },

    /**
     * Get the filePicker window
     */
    getWindow: function () {
        return KikCMS.windowManager.getWindow(this.instance + 'FilePicker', this.$element);
    }
});