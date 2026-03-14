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
        this._submenuTimeout = null;
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
        const container = button.closest('[class*="mb-1"]');
        const panel = container.querySelector('[data-navbar-target="accordionPanel"]');
        const icon = button.querySelector('[data-navbar-target="accordionIcon"]');

        if (panel) {
            panel.classList.toggle('hidden');
        }
        if (icon) {
            icon.classList.toggle('rotate-180');
        }
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
                dropdown.classList.add('hidden');
            }
        }, 150);
    }

    // --- Desktop submenu ---

    showSubmenu(event) {
        clearTimeout(this._submenuTimeout);
        const container = event.currentTarget;
        const submenu = container.querySelector('[data-navbar-target="submenu"]');
        if (submenu) {
            this._hideAllSubmenus(container.closest('[data-navbar-target="dropdown"]'));
            submenu.classList.remove('hidden');
        }
    }

    scheduleHideSubmenu(event) {
        const container = event.currentTarget;
        this._submenuTimeout = setTimeout(() => {
            const submenu = container.querySelector('[data-navbar-target="submenu"]');
            if (submenu) {
                submenu.classList.add('hidden');
            }
        }, 150);
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

    _hideAllSubmenus(parentDropdown) {
        if (!parentDropdown) return;
        parentDropdown.querySelectorAll('[data-navbar-target="submenu"]').forEach(s => s.classList.add('hidden'));
    }

    _clickOutside(event) {
        if (!this.element.contains(event.target)) {
            this._hideAllDropdowns();
        }
    }
}
