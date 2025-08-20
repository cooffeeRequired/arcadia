$(function () {
    const $btn     = $('#mobile-menu-button');
    const $sidebar = $('#sidebar');
    const $overlay = $('#sidebar-overlay');

    if ($btn.length && $sidebar.length) {
        $btn.on('click', function () {
            $sidebar.toggleClass('-translate-x-full');
            if ($overlay.length) $overlay.toggleClass('hidden');
        });

        if ($overlay.length) {
            $overlay.on('click', function () {
                $sidebar.addClass('-translate-x-full');
                $overlay.addClass('hidden');
            });
        }
    }
});
