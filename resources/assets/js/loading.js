(function ($) {
    // Globální loading state
    window.setLoading = function (show, text = 'Načítání...') {
        const $overlay = $('#global-loading-overlay');
        const $loadingText = $('#global-loading-text');

        if (show) {
            // Ujistit se, že overlay je nad vším
            $overlay.css({
                zIndex: 9999,
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0
            });

            $loadingText.text(text);
            $overlay.removeClass('hidden');
        } else {
            $overlay.addClass('hidden');
        }
    };

    // Helper funkce pro rychlé použití
    window.showLoading = function (text = 'Načítání...', autoHide = null) {
        window.setLoading(true, text);

        // Automatické skrytí
        if (autoHide) {
            const milliseconds = parseTimeToMilliseconds(autoHide);
            setTimeout(() => window.setLoading(false), milliseconds);
        }
    };

    window.hideLoading = function () {
        window.setLoading(false);
    };

    // Funkce pro parsování času (stejná jako v PHP)
    function parseTimeToMilliseconds(timeString) {
        timeString = $.trim(timeString).toLowerCase();

        // Regex pro parsování čísla a jednotky
        const match = timeString.match(/^(\d+(?:\.\d+)?)\s*(ms|s|m|h|d)$/);
        if (match) {
            const value = parseFloat(match[1]);
            const unit = match[2];

            switch (unit) {
                case 'ms': return Math.floor(value);
                case 's':  return Math.floor(value * 1000);
                case 'm':  return Math.floor(value * 60 * 1000);
                case 'h':  return Math.floor(value * 60 * 60 * 1000);
                case 'd':  return Math.floor(value * 24 * 60 * 60 * 1000);
                default:   return 1000;
            }
        }

        // Fallback
        return 1000;
    }
})(jQuery);
