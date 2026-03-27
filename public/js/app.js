/**
 * VQ Money — Application JavaScript
 */
(function ($) {
    'use strict';

    /* ======================================================================
       CSRF Helper
       ====================================================================== */
    const App = {
        /**
         * Read the CSRF token from the <meta> tag.
         */
        csrfToken: function () {
            const el = document.querySelector('meta[name="csrf-token"]');
            return el ? el.getAttribute('content') : '';
        },

        /**
         * Wrapper around jQuery.ajax that automatically injects the CSRF token.
         *
         * @param {object} options  Standard jQuery.ajax settings.
         * @returns {jqXHR}
         */
        ajax: function (options) {
            options = options || {};
            options.headers = Object.assign({}, options.headers, {
                'X-CSRF-TOKEN': App.csrfToken()
            });
            return $.ajax(options);
        }
    };

    // Expose globally
    window.App = App;

    /* ======================================================================
       DOM-ready initialisation
       ====================================================================== */
    $(function () {
        initFlashMessages();
        initSidebarToggle();
        initActiveNavLinks();
        initConfirmDelete();
        initSearchForm();
        initTooltips();
    });

    /* ======================================================================
       Flash Messages — auto-dismiss after 5 seconds
       ====================================================================== */
    function initFlashMessages() {
        var $flashes = $('.flash-message');
        if (!$flashes.length) return;

        setTimeout(function () {
            $flashes.each(function () {
                var $el = $(this);
                $el.addClass('fade-out');
                // Remove from DOM after the animation completes
                setTimeout(function () {
                    $el.alert('close');
                }, 500);
            });
        }, 5000);
    }

    /* ======================================================================
       Sidebar Toggle — mobile hamburger
       ====================================================================== */
    function initSidebarToggle() {
        var $toggle  = $('#sidebarToggle');
        var $sidebar = $('#sidebar');
        var $body    = $('body');

        // Create overlay element for mobile
        if (!$('.sidebar-overlay').length) {
            $body.append('<div class="sidebar-overlay" id="sidebarOverlay"></div>');
        }
        var $overlay = $('#sidebarOverlay');

        $toggle.on('click', function () {
            $sidebar.toggleClass('show');
            $overlay.toggleClass('show');
            var expanded = $sidebar.hasClass('show');
            $toggle.attr('aria-expanded', expanded);
        });

        // Close sidebar when clicking the overlay
        $overlay.on('click', function () {
            $sidebar.removeClass('show');
            $overlay.removeClass('show');
            $toggle.attr('aria-expanded', 'false');
        });

        // Close sidebar on Escape key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && $sidebar.hasClass('show')) {
                $sidebar.removeClass('show');
                $overlay.removeClass('show');
                $toggle.attr('aria-expanded', 'false');
                $toggle.focus();
            }
        });
    }

    /* ======================================================================
       Active Nav Link Highlighting
       ====================================================================== */
    function initActiveNavLinks() {
        var path = window.location.pathname;

        $('.sidebar-nav-link').each(function () {
            var $link = $(this);
            var href  = $link.attr('href');

            if (!href || href === '#' || href.charAt(0) !== '/') return;

            // Remove any server-side 'active' class first
            $link.removeClass('active');

            // Exact match for dashboard, prefix match for everything else
            if (href === '/dashboard' && (path === '/' || path === '/dashboard')) {
                $link.addClass('active');
            } else if (href !== '/dashboard' && path.indexOf(href) === 0) {
                $link.addClass('active');
            }
        });
    }

    /* ======================================================================
       Confirm Delete Dialogs
       ====================================================================== */
    function initConfirmDelete() {
        $(document).on('click', '[data-confirm-delete]', function (e) {
            var message = $(this).data('confirm-delete') || 'Are you sure you want to delete this item?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });

        // Also handle forms with data-confirm attribute
        $(document).on('submit', 'form[data-confirm]', function (e) {
            var message = $(this).data('confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    }

    /* ======================================================================
       Search Form
       ====================================================================== */
    function initSearchForm() {
        var $form = $('.top-bar-search');
        $form.on('submit', function () {
            var $input = $(this).find('input[name="q"]');
            // If search is empty, just go to /expenses without q param
            if ($.trim($input.val()) === '') {
                window.location.href = '/expenses';
                return false;
            }
        });
    }

    /* ======================================================================
       Tooltip Initialization
       ====================================================================== */
    function initTooltips() {
        var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(function (el) {
            new bootstrap.Tooltip(el);
        });
    }

})(jQuery);
