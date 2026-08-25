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
