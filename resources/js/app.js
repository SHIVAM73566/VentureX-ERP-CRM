// Standard Alpine is required: the application uses inline expressions such
// as `open = false`, `localStorage.setItem(...)` and `navigator.clipboard`
// which the CSP build cannot parse. SecurityHeaders allows 'unsafe-eval' on
// HTML responses only; API/JSON responses are always served without it.
import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.store('theme', {
        dark: localStorage.getItem('theme') === 'dark',
        toggle() {
            this.dark = !this.dark;
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
            this.apply();
        },
        apply() {
            document.documentElement.classList.toggle('dark', this.dark);
        },
    });

    Alpine.data('sidebar', () => ({
        open: false,
        collapsed: localStorage.getItem('sidebar') === 'collapsed',
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('sidebar', this.collapsed ? 'collapsed' : 'expanded');
        },
        mobileToggle() {
            this.open ? this.closeDrawer() : this.openDrawer();
        },
        openDrawer() {
            this.open = true;
            this.lockScroll();
            this.$nextTick(() => {
                this.$refs.drawer?.querySelector('a, button')?.focus();
            });
        },
        closeDrawer() {
            this.open = false;
            this.lockScroll();
            this.$nextTick(() => {
                this.$refs.menuButton?.focus();
            });
        },
        lockScroll() {
            document.body.style.overflow = this.open ? 'hidden' : '';
        },
        init() {
            const nav = this.$refs.nav;
            if (nav) {
                try {
                    const saved = parseInt(sessionStorage.getItem('sidebar-scroll') || '0', 10);
                    if (!Number.isNaN(saved) && saved > 0) {
                        nav.scrollTop = saved;
                    }
                } catch (e) {}
                this.$refs.nav.querySelector('a[aria-current="page"]')?.scrollIntoView({ block: 'nearest' });
                nav.addEventListener('scroll', () => {
                    try {
                        sessionStorage.setItem('sidebar-scroll', String(nav.scrollTop));
                    } catch (e) {}
                }, { passive: true });
            }

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.open) {
                    this.closeDrawer();
                }
            });
        },
    }));

    Alpine.data('dropdown', () => ({
        open: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
    }));

    Alpine.directive('submit', (el, { expression }, { effect, cleanup }) => {
        el.addEventListener('submit', () => {
            const btn = el.querySelector('[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.dataset.originalText = btn.innerHTML;
                btn.innerHTML = '<svg class="animate-spin h-4 w-4 inline mr-1" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Saving...';
            }
        });
    });
});

Alpine.start();

/*
 * Native fallback for the global product guide.
 *
 * The launcher, backdrop and X button are also wired to Alpine, but a stale
 * bundle or a single expression error must never trap a user in an overlay.
 * These delegated listeners work with plain DOM APIs and update Alpine state
 * when it is available.
 */
(function () {
    const setGuideOpen = function (open) {
        const root = document.querySelector('[data-guide-root]');
        if (!root) {
            return;
        }

        const data = window.Alpine ? window.Alpine.$data(root) : null;
        if (data) {
            data.open = open;
        }

        // Hide the whole overlay, not only the panel. This keeps the backdrop
        // from trapping clicks when Alpine is stale, and allows a clean reopen.
        const overlay = root.querySelector('[data-guide-overlay]');
        if (overlay) {
            overlay.style.display = open ? '' : 'none';
            overlay.setAttribute('aria-hidden', open ? 'false' : 'true');
        }

        root.querySelectorAll('[data-guide-panel]').forEach(function (el) {
            el.style.display = open ? '' : 'none';
        });
    };

    document.addEventListener('click', function (event) {
        if (event.target.closest('[data-guide-open]')) {
            setGuideOpen(true);
            return;
        }

        const closer = event.target.closest('[data-guide-close]');
        if (closer) {
            event.preventDefault();
            event.stopPropagation();
            setGuideOpen(false);
            return;
        }

        // Generic overlay fallback used by modals/drawers across the app.
        const overlayCloser = event.target.closest('[data-close-overlay]');
        if (overlayCloser) {
            event.preventDefault();
            event.stopPropagation();
            const scope = overlayCloser.closest('[x-data]');
            const data = scope && window.Alpine ? window.Alpine.$data(scope) : null;
            if (data && 'open' in data) {
                data.open = false;
            }
            const overlay = overlayCloser.closest('[data-overlay]') || scope;
            if (overlay) {
                overlay.style.display = 'none';
            }
        }
    }, true);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setGuideOpen(false);
        }
    });
}());
