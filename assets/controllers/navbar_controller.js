import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'mobileMenu', 'hamburgerIcon', 'closeIcon',
        'dropdownContainer', 'dropdown',
        'submenuContainer', 'submenu',
        'accordionIcon', 'accordionPanel',
        'userDropdownContainer', 'userDropdown',
    ];

    connect() {
        this._hideTimeout = null;
        // WeakMap pour un timer indépendant par conteneur de sous-menu
        this._submenuTimeouts = new WeakMap();
        this._userDropdownTimeout = null;
        this._boundClickOutside = this._clickOutside.bind(this);
        document.addEventListener('click', this._boundClickOutside);
    }

    disconnect() {
        document.removeEventListener('click', this._boundClickOutside);
    }

    // --- Mobile ---

    toggleMobile() {
        const menu = this.mobileMenuTarget;
        const isHidden = menu.classList.contains('hidden');

        menu.classList.toggle('hidden', !isHidden);
        this.hamburgerIconTarget.classList.toggle('hidden', isHidden);
        this.closeIconTarget.classList.toggle('hidden', !isHidden);
    }

    toggleAccordion(event) {
        const button = event.currentTarget;
        const item = button.closest('[data-accordion-item]');
        if (!item) return;
        const panel = item.querySelector(':scope > [data-navbar-target="accordionPanel"]');
        const icon = button.querySelector('[data-navbar-target="accordionIcon"]');

        if (panel) panel.classList.toggle('hidden');
        if (icon) icon.classList.toggle('rotate-180');
    }

    // --- Desktop dropdown ---

    showDropdown(event) {
        clearTimeout(this._hideTimeout);
        const container = event.currentTarget;
        const dropdown = container.querySelector('[data-navbar-target="dropdown"]');
        if (dropdown) {
            this._hideAllDropdowns();
            dropdown.classList.remove('hidden');
        }
    }

    scheduleHideDropdown(event) {
        const container = event.currentTarget;
        this._hideTimeout = setTimeout(() => {
            const dropdown = container.querySelector('[data-navbar-target="dropdown"]');
            if (dropdown) {
                // Ferme récursivement tous les sous-menus imbriqués
                dropdown.querySelectorAll('[data-navbar-target="submenu"]')
                    .forEach(s => s.classList.add('hidden'));
                dropdown.classList.add('hidden');
            }
        }, 150);
    }

    // --- Desktop submenu ---

    showSubmenu(event) {
        // Annule aussi le timer du dropdown principal pour éviter qu'il se ferme
        clearTimeout(this._hideTimeout);

        const container = event.currentTarget;
        clearTimeout(this._submenuTimeouts.get(container));

        const submenu = container.querySelector(':scope > [data-navbar-target="submenu"]');
        if (!submenu) return;

        // Cache les sous-menus frères (et leurs enfants imbriqués)
        const parentList = container.parentElement;
        if (parentList) {
            parentList
                .querySelectorAll(':scope > [data-navbar-target="submenuContainer"] > [data-navbar-target="submenu"]')
                .forEach(s => {
                    s.querySelectorAll('[data-navbar-target="submenu"]').forEach(ns => ns.classList.add('hidden'));
                    s.classList.add('hidden');
                });
        }

        submenu.classList.remove('hidden');
    }

    scheduleHideSubmenu(event) {
        const container = event.currentTarget;
        const timeout = setTimeout(() => {
            const submenu = container.querySelector(':scope > [data-navbar-target="submenu"]');
            if (submenu) {
                // Ferme récursivement tous les sous-menus imbriqués
                submenu.querySelectorAll('[data-navbar-target="submenu"]').forEach(s => s.classList.add('hidden'));
                submenu.classList.add('hidden');
            }
        }, 150);
        this._submenuTimeouts.set(container, timeout);
    }

    // --- User dropdown ---

    showUserDropdown(event) {
        clearTimeout(this._userDropdownTimeout);
        if (this.hasUserDropdownTarget) {
            this.userDropdownTarget.classList.remove('hidden');
        }
    }

    scheduleHideUserDropdown(event) {
        this._userDropdownTimeout = setTimeout(() => {
            if (this.hasUserDropdownTarget) {
                this.userDropdownTarget.classList.add('hidden');
            }
        }, 150);
    }

    // --- Helpers ---

    _hideAllDropdowns() {
        this.dropdownTargets.forEach(d => d.classList.add('hidden'));
        if (this.hasUserDropdownTarget) {
            this.userDropdownTarget.classList.add('hidden');
        }
    }

    _clickOutside(event) {
        if (!this.element.contains(event.target)) {
            this._hideAllDropdowns();
        }
    }
}
