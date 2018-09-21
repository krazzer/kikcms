$(function () {
    scaleMenu();

    $(window).resize(scaleMenu);

    var cacheTreeSelectionKey = "kikcms.cacheTreeSelection";

    $('.tree').each(function () {
        var $tree       = $(this);
        var $checkboxes = $tree.find('input[type=checkbox]');

        var cacheTree = localStorage.getItem(cacheTreeSelectionKey);

        if (cacheTree) {
            cacheTree = JSON.parse(cacheTree);

            for (var i in cacheTree) {
                $tree.find('[id="' + cacheTree[i] + '"]').prop("checked", true);
            }
        }

        $checkboxes.change(function () {
            var checked = [];

            $tree.find('input[type=checkbox]:checked').each(function () {
                checked.push($(this).attr('id'));
            });

            localStorage.setItem(cacheTreeSelectionKey, JSON.stringify(checked));
        });
    })
});

/**
 * Make sure the menu's min height is enough to show everything on portrait mode on mobile devices
 */
function scaleMenu() {
    var $window = $(window);
    var scale   = $window.height() / $window.width();

    $('#menu').css('min-height', $('#main').outerWidth() * scale);
}