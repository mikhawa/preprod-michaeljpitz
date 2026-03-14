import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'modal', 'image', 'preview', 'hiddenInput'];

    connect() {
        this.cropper = null;
        this.loadCropperJS();
    }

    async loadCropperJS() {
        if (window.Cropper) {
            return;
        }

        const script = document.createElement('script');
        script.src = '/js/cropper.min.js';
        script.async = true;
        document.head.appendChild(script);
    }

    disconnect() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    }

    openFileDialog() {
        if (this.hasInputTarget) {
            this.inputTarget.click();
        }
    }

    selectImage(event) {
        const file = event.target.files[0];
        if (!file) {
            return;
        }

        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            alert('Veuillez sélectionner une image JPEG, PNG ou WebP.');
            this.inputTarget.value = '';
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('L\'image ne doit pas dépasser 10 Mo.');
            this.inputTarget.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            this.imageTarget.src = e.target.result;
            this.showModal();
            this.waitForCropperAndInit();
        };
        reader.readAsDataURL(file);
    }

    waitForCropperAndInit() {
        if (window.Cropper) {
            this.initCropper();
        } else {
            setTimeout(() => this.waitForCropperAndInit(), 100);
        }
    }

    showModal() {
        this.modalTarget.style.display = 'flex';
        this.modalTarget.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    hideModal() {
        this.modalTarget.style.display = 'none';
        this.modalTarget.classList.add('hidden');
        document.body.style.overflow = '';
    }

    initCropper() {
        if (this.cropper) {
            this.cropper.destroy();
        }

        this.cropper = new window.Cropper(this.imageTarget, {
            aspectRatio: 1,
            viewMode: 1,
            minCropBoxWidth: 100,
            minCropBoxHeight: 100,
            dragMode: 'move',
            autoCropArea: 0.8,
            cropBoxResizable: true,
            background: false,
            guides: true,
            center: true,
            highlight: true,
            responsive: true,
        });
    }

    crop() {
        if (!this.cropper) {
            return;
        }

        const canvas = this.cropper.getCroppedCanvas({
            width: 320,
            height: 320,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });

        if (!canvas) {
            alert('Erreur lors du recadrage de l\'image.');
            return;
        }

        const base64 = canvas.toDataURL('image/jpeg', 0.9);

        this.hiddenInputTarget.value = base64;

        this.updatePreview(base64);

        this.hideModal();
        this.destroyCropper();
    }

    updatePreview(base64) {
        if (this.hasPreviewTarget) {
            if (this.previewTarget.tagName === 'IMG') {
                this.previewTarget.src = base64;
            } else {
                const img = document.createElement('img');
                img.src = base64;
                img.className = 'w-20 h-20 rounded-full object-cover';
                img.dataset.avatarCropTarget = 'preview';
                this.previewTarget.replaceWith(img);
            }
        }
    }

    cancel() {
        this.hideModal();
        this.destroyCropper();
        this.inputTarget.value = '';
    }

    destroyCropper() {
        if (this.cropper) {
            this.cropper.destroy();
            this.cropper = null;
        }
    }

    closeModalOnClickOutside(event) {
        if (event.target === this.modalTarget) {
            this.cancel();
        }
    }
}
