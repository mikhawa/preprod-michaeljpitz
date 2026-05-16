import { Controller } from '@hotwired/stimulus';

const IS_DESKTOP = () => window.matchMedia('(min-width: 768px)').matches;

export default class extends Controller {
    static targets = ['panel'];

    connect() {
        this._hideTimer = null;
        // Auto-ouvre les panneaux contenant la catégorie active, avec positionnement desktop
        this.panelTargets.forEach(panel => {
            if (!panel.querySelector('[data-active]')) return;

            const groupId = panel.dataset.groupId;
            const scope = panel.closest('.cf-level');

            if (scope && IS_DESKTOP()) {
                // Retrouve le déclencheur (dans la ligne de pills, pas le panel lui-même)
                const trigger = scope.querySelector(`:scope > div > [data-group-id="${groupId}"]`);
                if (trigger) {
                    const offset = Math.round(
                        trigger.getBoundingClientRect().left - scope.getBoundingClientRect().left
                    );
                    panel.style.marginLeft = offset + 'px';
                }
            }

            panel.classList.add('cf-open');
        });
    }

    disconnect() {
        clearTimeout(this._hideTimer);
    }

    // Desktop : survol du déclencheur → aligne et ouvre son panneau
    show(event) {
        clearTimeout(this._hideTimer);
        const trigger = event.currentTarget;
        const groupId = trigger.dataset.groupId;
        const scope = trigger.closest('.cf-level');
        if (!scope) return;

        // Ferme les panneaux frères et leurs descendants
        scope.querySelectorAll(':scope > .cf-panel').forEach(panel => {
            panel.style.marginLeft = '';
            panel.querySelectorAll('.cf-panel').forEach(sub => sub.classList.remove('cf-open'));
            panel.classList.remove('cf-open');
        });

        const target = scope.querySelector(`:scope > .cf-panel[data-group-id="${groupId}"]`);
        if (!target) return;

        // Desktop uniquement : aligne le panneau sous le pill déclencheur
        if (IS_DESKTOP()) {
            const offset = Math.round(
                trigger.getBoundingClientRect().left - scope.getBoundingClientRect().left
            );
            target.style.marginLeft = offset + 'px';
        }

        target.classList.add('cf-open');
    }

    // Fermeture différée à la sortie de la nav
    scheduleHide() {
        this._hideTimer = setTimeout(() => {
            this.panelTargets.forEach(p => {
                p.style.marginLeft = '';
                p.classList.remove('cf-open');
            });
        }, 200);
    }

    // Annule la fermeture quand la souris réentre
    cancelHide() {
        clearTimeout(this._hideTimer);
    }

    // Mobile : clic sur le chevron → bascule le panneau (sans repositionnement)
    toggle(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const groupId = button.dataset.groupId;
        const scope = button.closest('.cf-level');
        if (!scope) return;

        const target = scope.querySelector(`:scope > .cf-panel[data-group-id="${groupId}"]`);
        if (!target) return;

        const isOpen = target.classList.contains('cf-open');
        scope.querySelectorAll(':scope > .cf-panel').forEach(panel => {
            panel.querySelectorAll('.cf-panel').forEach(sub => sub.classList.remove('cf-open'));
            panel.classList.remove('cf-open');
        });
        if (!isOpen) target.classList.add('cf-open');
    }
}
