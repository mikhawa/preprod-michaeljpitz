import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['slide'];
    static values = {
        interval: { type: Number, default: 3000 }
    };

    connect() {
        this.currentIndex = 0;
        this.slideTargets.forEach((slide, i) => {
            slide.style.opacity = i === 0 ? '1' : '0';
        });
        this.timer = setInterval(() => this.next(), this.intervalValue);
    }

    disconnect() {
        clearInterval(this.timer);
    }

    next() {
        const slides = this.slideTargets;
        slides[this.currentIndex].style.opacity = '0';
        this.currentIndex = (this.currentIndex + 1) % slides.length;
        slides[this.currentIndex].style.opacity = '1';
    }
}
