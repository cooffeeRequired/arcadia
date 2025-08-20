(function ($) {
    // Global Modal System (jQuery)
    function GlobalModal() {
        this.$modal     = $('#global-modal');
        this.$container = this.$modal.find('.modal-container');
        this.$title     = $('#modal-title');
        this.$body      = $('#modal-body');
        this.$footer    = $('#modal-footer');
        this.$closeBtn  = $('#modal-close');
        this.$cancelBtn = $('#modal-cancel');

        this.onCloseCallback = null;
        this.bindEvents();
    }

    GlobalModal.prototype.bindEvents = function () {
        const self = this;

        // Zavření modalu kliknutím na overlay
        this.$modal.on('click', function (e) {
            if (e.target === self.$modal.get(0)) {
                self.hide();
            }
        });

        // Zavření modalu kliknutím na tlačítka
        this.$closeBtn.on('click', function () { self.hide(); });
        this.$cancelBtn.on('click', function () { self.hide(); });

        // Zavření modalu klávesou Escape
        $(document).on('keydown.globalModal', function (e) {
            if (e.key === 'Escape' && self.isVisible()) {
                self.hide();
            }
        });
    };

    GlobalModal.prototype.show = function () {
        this.$modal.addClass('show');
        $('body').css('overflow', 'hidden');
    };

    GlobalModal.prototype.hide = function () {
        this.$modal.removeClass('show');
        $('body').css('overflow', '');
        if (typeof this.onCloseCallback === 'function') {
            this.onCloseCallback();
            this.onCloseCallback = null; // použij jednorázově
        }
    };

    GlobalModal.prototype.isVisible = function () {
        return this.$modal.hasClass('show');
    };

    GlobalModal.prototype.setTitle = function (title) {
        this.$title.text(title);
    };

    GlobalModal.prototype.setContent = function (content) {
        this.$body.html(content);
    };

    GlobalModal.prototype.setFooter = function (footerHtml) {
        this.$footer.html(footerHtml);
        // Přebinduj cancel po výměně patičky
        const self = this;
        this.$footer.find('#modal-cancel').on('click', function () { self.hide(); });
    };

    GlobalModal.prototype.showLoading = function () {
        this.$body.html('<div class="modal-loading">Načítání...</div>');
    };

    GlobalModal.prototype.setSize = function (size) {
        this.$container.attr('style', ''); // reset inline stylů
        let width = '600px';
        if (size === 'small') width = '400px';
        else if (size === 'large') width = '800px';
        else if (size === 'xlarge') width = '1200px';
        else if (size === 'fullscreen') width = '100%';
        this.$container.css('width', width);
    };

    GlobalModal.prototype.loadModal = function (options) {
        const opts = $.extend({
            title: 'Název modalu',
            url: null,
            content: null,
            footer: null,
            size: 'medium',
            onLoad: null,
            onClose: null
        }, options || {});

        this.setSize(opts.size);
        this.setTitle(opts.title);
        this.onCloseCallback = typeof opts.onClose === 'function' ? opts.onClose : null;

        if (opts.footer) {
            this.setFooter(opts.footer);
        } else {
            this.setFooter('<button type="button" class="btn-secondary" id="modal-cancel">Zavřít</button>');
        }

        const self = this;

        if (opts.url) {
            this.showLoading();
            this.show();

            $.get(opts.url)
                .done(function (html) {
                    self.setContent(html);
                    if (typeof opts.onLoad === 'function') opts.onLoad(self);
                })
                .fail(function (xhr, status, error) {
                    self.setContent('<div class="text-red-600 p-4">Chyba při načítání: ' + (error || status) + '</div>');
                });
        } else if (opts.content) {
            this.setContent(opts.content);
            this.show();
            if (typeof opts.onLoad === 'function') opts.onLoad(self);
        } else {
            this.showLoading();
            this.show();
        }
    };

    // Inicializace a globální helpery
    const globalModal = new GlobalModal();

    window.loadModal = function (options) {
        globalModal.loadModal(options);
    };

    window.showModal = function (title, content, size) {
        globalModal.loadModal({
            title: title,
            content: content,
            size: size || 'medium'
        });
    };

    window.hideModal = function () {
        globalModal.hide();
    };
})(jQuery);
