function changeLanguage(locale, flag) {
    const $overlay = $(`#language-loading-overlay`);
    const $flagAnimation = $(`#flag-animation`);
    const $languageText = $(`#language-text`);

    $overlay.css('z-index', 0);
    $overlay.css('position', 'fixed');
    $overlay.css('top', 0);
    $overlay.css('left', 0);
    $overlay.css('right', 0);
    $overlay.css('bottom', 0);
    $overlay.removeClass('hidden');

    const language_ = locale;

    $languageText.text(i18('theme-changer.changing', language_))
    $flagAnimation.addClass('flag-morph');
    setTimeout(() => $flagAnimation.text(flag), 600)
    setTimeout(() => $flagAnimation.removeClass('flag-morph'), 1200)

    $.ajax('/api/languages/set', {
        method: 'POST',
        headers: {
            'Accept': 'application/json'
        },
        contentType: 'application/json',
        data: JSON.stringify({locale: locale, language: language_}),
        success: (response) => {
            if (response.success) {
                setTimeout(() => window.location.reload(), 1000);
            } else {
                $overlay.addClass('hidden');
            }
        },
        error: () => {
            $overlay.addClass('hidden');
        }
    });
}

