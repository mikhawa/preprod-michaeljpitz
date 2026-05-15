import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['parentGroup', 'panel'];

    connect() {
        this._hideTimer = null;
        // Auto-ouvre le panneau si une catégorie enfant est active au chargement
        this.panelTargets.forEach(panel => {
            if (panel.querySelector('[data-active]')) {
                panel.classList.add('cf-open');
            }
        });
    }

    disconnect() {
        clearTimeout(this._hideTimer);
    }

    // Desktop : survol du groupe parent → affiche ses enfants
    show(event) {
        clearTimeout(this._hideTimer);
        const id = event.currentTarget.dataset.groupId;
        this.panelTargets.forEach(panel => {
            if (panel.dataset.groupId === id) {
                panel.classList.add('cf-open');
            } else {
                panel.classList.remove('cf-open');
            }
        });
    }

    scheduleHide() {
        this._hideTimer = setTimeout(() => this.hideAll(), 150);
    }

    cancelHide() {
        clearTimeout(this._hideTimer);
    }

    hideAll() {
        this.panelTargets.forEach(panel => panel.classList.remove('cf-open'));
    }

    // Mobile : clic sur le chevron → bascule les enfants
    toggle(event) {
        event.preventDefault();
        const id = event.currentTarget.dataset.groupId;
        const panel = this.panelTargets.find(p => p.dataset.groupId === id);
        if (!panel) return;

        const isOpen = panel.classList.contains('cf-open');
        this.panelTargets.forEach(p => p.classList.remove('cf-open'));
        if (!isOpen) panel.classList.add('cf-open');
    }
}
