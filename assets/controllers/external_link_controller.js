import { Controller } from '@hotwired/stimulus';

// Ouvre les liens externes dans un nouvel onglet
export default class extends Controller {
    connect() {
        this.element.querySelectorAll('a[href]').forEach(link => {
            if (link.hostname && link.hostname !== window.location.hostname) {
                link.setAttribute('target', '_blank');
                link.setAttribute('rel', 'noopener noreferrer');
            }
        });
    }
}
