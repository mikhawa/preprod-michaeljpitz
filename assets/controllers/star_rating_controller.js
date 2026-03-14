import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'stars'];

    connect() {
        this.updateDisplay(parseInt(this.inputTarget.value) || 0);
    }

    select(event) {
        const value = parseInt(event.currentTarget.dataset.starRatingValueParam);
        this.inputTarget.value = value;
        this.updateDisplay(value);
        this.element.submit();
    }

    updateDisplay(selectedValue) {
        const buttons = this.starsTarget.querySelectorAll('.star-btn svg');
        buttons.forEach((svg, index) => {
            if (index < selectedValue) {
                svg.classList.add('text-yellow-400');
                svg.classList.remove('text-gray-300');
            } else {
                svg.classList.remove('text-yellow-400');
                svg.classList.add('text-gray-300');
            }
        });
    }
}
