import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['panel'];

    connect() {
        this._hideTimer = null;
        // Auto-ouvre les panneaux contenant la catégorie active (tous les niveaux)
        this.panelTargets.forEach(panel => {
            if (panel.querySelector('[data-active]')) {
                panel.classList.add('cf-open');
            }
        });
    }

    disconnect() {
        clearTimeout(this._hideTimer);
    }

    // Desktop : survol du déclencheur → ouvre son panneau enfant
    show(event) {
        clearTimeout(this._hideTimer);
        const trigger = event.currentTarget;
        const groupId = trigger.dataset.groupId;
        const scope = trigger.closest('.cf-level');
        if (!scope) return;
        this._openIn(scope, groupId);
    }

    // Fermeture différée à la sortie de la nav (niveau racine)
    scheduleHide() {
        this._hideTimer = setTimeout(() => {
            this.panelTargets.forEach(p => p.classList.remove('cf-open'));
        }, 200);
    }

    // Annule la fermeture quand la souris réentre dans la nav
    cancelHide() {
        clearTimeout(this._hideTimer);
    }

    // Mobile : clic sur le chevron → bascule le panneau enfant
    toggle(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const groupId = button.dataset.groupId;
        const scope = button.closest('.cf-level');
        if (!scope) return;

        const target = scope.querySelector(`:scope > .cf-panel[data-group-id="${groupId}"]`);
        if (!target) return;

        const isOpen = target.classList.contains('cf-open');
        // Ferme les panneaux frères et leurs descendants
        scope.querySelectorAll(':scope > .cf-panel').forEach(panel => {
            panel.querySelectorAll('.cf-panel').forEach(sub => sub.classList.remove('cf-open'));
            panel.classList.remove('cf-open');
        });
        if (!isOpen) target.classList.add('cf-open');
    }

    _openIn(scope, groupId) {
        // Ferme les panneaux frères et leurs descendants imbriqués
        scope.querySelectorAll(':scope > .cf-panel').forEach(panel => {
            panel.querySelectorAll('.cf-panel').forEach(sub => sub.classList.remove('cf-open'));
            panel.classList.remove('cf-open');
        });
        // Ouvre le panneau cible
        const target = scope.querySelector(`:scope > .cf-panel[data-group-id="${groupId}"]`);
        if (target) target.classList.add('cf-open');
    }
}
