$(function () {
    scaleMenu();

    $(window).resize(scaleMenu);
});

/**
 * Make sure the menu's min height is enough to show everything on portrait mode on mobile devices
 */
function scaleMenu() {
    var $window = $(window);
    var scale = $window.height() / $window.width();

    $('#menu').css('min-height', $('#main').outerWidth() * scale);
}