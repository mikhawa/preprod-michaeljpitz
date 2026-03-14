import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.images = [];
        this.currentIndex = 0;
        this.modal = null;
        this.isFullSize = false;

        this._collectImages();
        this._bindImageClicks();
        this._handleKeydown = this._handleKeydown.bind(this);
    }

    disconnect() {
        this._removeModal();
        document.removeEventListener('keydown', this._handleKeydown);
    }

    _collectImages() {
        const contentImages = this.element.querySelectorAll('img');
        contentImages.forEach((img) => {
            this.images.push({
                src: img.src,
                alt: img.alt || '',
            });
        });
    }

    _bindImageClicks() {
        const contentImages = this.element.querySelectorAll('img');
        contentImages.forEach((img, index) => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', (e) => {
                e.preventDefault();
                this._openLightbox(index);
            });
        });
    }

    _openLightbox(index) {
        this.currentIndex = index;
        this.isFullSize = false;
        this._createModal();
        this._updateImage();
        document.addEventListener('keydown', this._handleKeydown);
        document.body.style.overflow = 'hidden';
    }

    _closeLightbox() {
        this._removeModal();
        document.removeEventListener('keydown', this._handleKeydown);
        document.body.style.overflow = '';
    }

    _createModal() {
        this.modal = document.createElement('div');
        this.modal.className = 'lightbox-modal';
        this.modal.innerHTML = `
            <div class="lightbox-overlay"></div>
            <div class="lightbox-container">
                <div class="lightbox-toolbar">
                    <button class="lightbox-btn lightbox-zoom" aria-label="Agrandir" title="Voir en taille réelle">
                        <svg class="icon-expand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/>
                        </svg>
                        <svg class="icon-shrink" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                            <path d="M4 14h6v6M14 4h6v6M4 14l7-7M20 10l-7 7"/>
                        </svg>
                    </button>
                    <button class="lightbox-btn lightbox-close" aria-label="Fermer" title="Fermer">&times;</button>
                </div>
                <button class="lightbox-nav lightbox-prev" aria-label="Image précédente">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
                <div class="lightbox-content">
                    <img class="lightbox-image" src="" alt="">
                </div>
                <button class="lightbox-nav lightbox-next" aria-label="Image suivante">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18l6-6-6-6"/>
                    </svg>
                </button>
                <div class="lightbox-counter"></div>
            </div>
        `;

        this._addStyles();

        // Événements
        this.modal.querySelector('.lightbox-overlay').addEventListener('click', () => this._closeLightbox());
        this.modal.querySelector('.lightbox-close').addEventListener('click', () => this._closeLightbox());
        this.modal.querySelector('.lightbox-prev').addEventListener('click', () => this._prevImage());
        this.modal.querySelector('.lightbox-next').addEventListener('click', () => this._nextImage());
        this.modal.querySelector('.lightbox-zoom').addEventListener('click', () => this._toggleZoom());

        // Vérifier la taille après chargement
        this.modal.querySelector('.lightbox-image').addEventListener('load', () => this._checkImageSize());

        document.body.appendChild(this.modal);

        requestAnimationFrame(() => {
            this.modal.classList.add('lightbox-open');
        });
    }

    _removeModal() {
        if (this.modal) {
            this.modal.classList.remove('lightbox-open');
            setTimeout(() => {
                this.modal?.remove();
                this.modal = null;
            }, 200);
        }
    }

    _updateImage() {
        if (!this.modal || this.images.length === 0) return;

        const image = this.images[this.currentIndex];
        const imgElement = this.modal.querySelector('.lightbox-image');
        const counter = this.modal.querySelector('.lightbox-counter');
        const zoomBtn = this.modal.querySelector('.lightbox-zoom');

        // Réinitialiser le zoom
        this.isFullSize = false;
        this._updateZoomState();
        zoomBtn.style.display = 'none';

        imgElement.src = image.src;
        imgElement.alt = image.alt;

        // Compteur
        if (this.images.length > 1) {
            counter.textContent = `${this.currentIndex + 1} / ${this.images.length}`;
            counter.style.display = 'block';
        } else {
            counter.style.display = 'none';
        }

        // Boutons de navigation
        const prevBtn = this.modal.querySelector('.lightbox-prev');
        const nextBtn = this.modal.querySelector('.lightbox-next');

        if (this.images.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        }
    }

    _prevImage() {
        if (this.images.length <= 1) return;
        this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
        this._updateImage();
    }

    _nextImage() {
        if (this.images.length <= 1) return;
        this.currentIndex = (this.currentIndex + 1) % this.images.length;
        this._updateImage();
    }

    _handleKeydown(e) {
        switch (e.key) {
            case 'Escape':
                if (this.isFullSize) {
                    this._toggleZoom();
                } else {
                    this._closeLightbox();
                }
                break;
            case 'ArrowLeft':
                this._prevImage();
                break;
            case 'ArrowRight':
                this._nextImage();
                break;
        }
    }

    _checkImageSize() {
        if (!this.modal) return;

        const img = this.modal.querySelector('.lightbox-image');
        const zoomBtn = this.modal.querySelector('.lightbox-zoom');

        // Afficher le bouton zoom si l'image est plus grande que l'affichage
        const isLarger = img.naturalWidth > img.clientWidth || img.naturalHeight > img.clientHeight;

        if (isLarger) {
            zoomBtn.style.display = 'flex';
            zoomBtn.title = `Voir en taille réelle (${img.naturalWidth} × ${img.naturalHeight})`;
        } else {
            zoomBtn.style.display = 'none';
        }
    }

    _toggleZoom() {
        this.isFullSize = !this.isFullSize;
        this._updateZoomState();
    }

    _updateZoomState() {
        if (!this.modal) return;

        const content = this.modal.querySelector('.lightbox-content');
        const img = this.modal.querySelector('.lightbox-image');
        const zoomBtn = this.modal.querySelector('.lightbox-zoom');
        const iconExpand = zoomBtn.querySelector('.icon-expand');
        const iconShrink = zoomBtn.querySelector('.icon-shrink');

        if (this.isFullSize) {
            content.classList.add('lightbox-fullsize');
            img.classList.add('lightbox-image-full');
            iconExpand.style.display = 'none';
            iconShrink.style.display = 'block';
            zoomBtn.title = 'Réduire';
            zoomBtn.setAttribute('aria-label', 'Réduire');
        } else {
            content.classList.remove('lightbox-fullsize');
            img.classList.remove('lightbox-image-full');
            iconExpand.style.display = 'block';
            iconShrink.style.display = 'none';
            zoomBtn.title = 'Voir en taille réelle';
            zoomBtn.setAttribute('aria-label', 'Agrandir');
        }
    }

    _addStyles() {
        if (document.getElementById('lightbox-styles')) return;

        const style = document.createElement('style');
        style.id = 'lightbox-styles';
        style.textContent = `
            .lightbox-modal {
                position: fixed;
                inset: 0;
                z-index: 9999;
                opacity: 0;
                transition: opacity 0.2s ease;
            }
            .lightbox-modal.lightbox-open {
                opacity: 1;
            }
            .lightbox-overlay {
                position: absolute;
                inset: 0;
                background: rgba(0, 0, 0, 0.9);
            }
            .lightbox-container {
                position: relative;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .lightbox-toolbar {
                position: absolute;
                top: 1rem;
                right: 1rem;
                display: flex;
                gap: 0.5rem;
                z-index: 10;
            }
            .lightbox-btn {
                width: 44px;
                height: 44px;
                background: rgba(255, 255, 255, 0.1);
                border: none;
                border-radius: 50%;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
            }
            .lightbox-btn:hover {
                background: rgba(255, 255, 255, 0.2);
            }
            .lightbox-close {
                font-size: 2rem;
            }
            .lightbox-zoom svg {
                width: 20px;
                height: 20px;
            }
            .lightbox-content {
                max-width: 90vw;
                max-height: 90vh;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }
            .lightbox-content.lightbox-fullsize {
                max-width: 100vw;
                max-height: 100vh;
                width: 100vw;
                height: 100vh;
                overflow: auto;
                align-items: flex-start;
                justify-content: flex-start;
                padding: 1rem;
            }
            .lightbox-image {
                max-width: 90vw;
                max-height: 85vh;
                object-fit: contain;
                border-radius: 4px;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
                transition: all 0.3s ease;
            }
            .lightbox-image.lightbox-image-full {
                max-width: none;
                max-height: none;
                width: auto;
                height: auto;
            }
            .lightbox-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                width: 50px;
                height: 50px;
                background: rgba(255, 255, 255, 0.1);
                border: none;
                border-radius: 50%;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: background 0.2s;
                z-index: 10;
            }
            .lightbox-nav:hover {
                background: rgba(255, 255, 255, 0.2);
            }
            .lightbox-prev {
                left: 1rem;
            }
            .lightbox-next {
                right: 1rem;
            }
            .lightbox-nav svg {
                width: 24px;
                height: 24px;
            }
            .lightbox-counter {
                position: absolute;
                bottom: 1rem;
                left: 50%;
                transform: translateX(-50%);
                color: white;
                font-size: 0.875rem;
                background: rgba(0, 0, 0, 0.5);
                padding: 0.5rem 1rem;
                border-radius: 9999px;
                z-index: 10;
            }
            @media (max-width: 640px) {
                .lightbox-nav {
                    width: 40px;
                    height: 40px;
                }
                .lightbox-nav svg {
                    width: 20px;
                    height: 20px;
                }
                .lightbox-btn {
                    width: 40px;
                    height: 40px;
                }
                .lightbox-zoom svg {
                    width: 18px;
                    height: 18px;
                }
            }
        `;
        document.head.appendChild(style);
    }
}
