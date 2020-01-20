/**
 * With this class you can create windows
 */
var WindowManager = Class.extend({
    windows: null,

    /**
     * @param $window
     * @param onCloseAction
     */
    closeWindow: function ($window, onCloseAction) {
        var $overlay = this.getOverlayContainer();

        var level = parseInt($window.data('level'));

        if (level == 0) {
            $('body').removeClass('datatableBlur');

            // fix weird tinymce issue, where menu cannot have position: fixed
            $('body > #menu').css('top', '0');
            $overlay.css('z-index', 3);
        } else {
            $('.dataTableWindow.level' + (level - 1)).removeClass('blur');
            $overlay.css('z-index', level + 2);
        }

        $('.dataTableWindow.level' + (level + 1)).remove();

        $window.fadeOut();
        $window.find('.windowContent').html('');

        if(typeof onCloseAction !== 'undefined'){
            onCloseAction();
        }
    },

    /**
     * @return {{length}|jQuery}
     */
    getNotFadingContainer: function () {
        var $bodyNotFading = $('body > #notFading');

        if (!$bodyNotFading.length) {
            $bodyNotFading = $('<div id="notFading"></div>');
            $('body').append($bodyNotFading);
        }

        return $bodyNotFading;
    },

    /**
     * @return {{length}|jQuery}
     */
    getOverlayContainer: function () {
        var $overlay = $('body > #overlay');

        if (!$overlay.length) {
            $overlay = $('<div id="overlay"></div>');
            $('body').prepend($overlay);
        }

        return $overlay;
    },

    /**
     * @param uniqueId
     * @param $baseElement
     * @param closeAction
     * @return {jQuery|HTMLElement}
     */
    getWindow: function (uniqueId, $baseElement, closeAction) {
        var self              = this;
        var $bodyNotFading    = this.getNotFadingContainer();
        var parentWindowLevel = $baseElement.parentsUntil('.dataTableWindow').parent().attr('data-level');
        var windowId          = uniqueId + 'Window';
        var level             = 0;

        if (parentWindowLevel) {
            level = parseInt(parentWindowLevel) + 1;
            windowId += 'Level' + level;
        }

        var $window = $bodyNotFading.find(' > #' + windowId);

        if (!$window.length) {
            $window = '<div class="dataTableWindow level' + level + '" data-level="' + level + '" id="' + windowId + '">' +
                '<div class="closeButton"></div><div class="windowContent"></div></div>';

            $bodyNotFading.prepend($window);
        } else {
            $bodyNotFading.find(' > #' + windowId).find('.closeButton').unbind("click");
        }

        $window = $('#' + windowId);

        $window.find('.closeButton').click(function () {
            if (typeof closeAction == 'undefined') {
                return self.closeWindow($window);
            } else {
                return closeAction();
            }
        });

        return $window;
    },

    /**
     * @param $window
     */
    showWindow: function ($window) {
        var $overlay = this.getOverlayContainer();

        var level = parseInt($window.attr('data-level'));

        if (level == 0) {
            $('body').addClass('datatableBlur');

            // fix weird tinymce issue, where menu cannot have position: fixed
            $('body > #menu').css('top', $(window).scrollTop());
        } else {
            $('.dataTableWindow.level' + (level - 1)).addClass('blur');
            $overlay.css('z-index', level + 3);
        }

        $window.fadeIn();
    }
});