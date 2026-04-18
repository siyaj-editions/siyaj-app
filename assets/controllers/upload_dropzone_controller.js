import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'name', 'preview', 'previewImage', 'zone'];
    static values = {
        emptyLabel: String,
    };

    connect() {
        this.updateLabel();
        this.updatePreview();
    }

    open() {
        this.inputTarget.click();
    }

    dragOver(event) {
        event.preventDefault();
        this.zoneTarget.classList.add('is-dragover');
    }

    dragLeave(event) {
        event.preventDefault();
        this.zoneTarget.classList.remove('is-dragover');
    }

    drop(event) {
        event.preventDefault();
        this.zoneTarget.classList.remove('is-dragover');

        const files = event.dataTransfer?.files;
        if (!files || files.length === 0) {
            return;
        }

        this.inputTarget.files = files;
        this.updateLabel();
        this.updatePreview();
    }

    updateLabel() {
        const file = this.inputTarget.files?.[0];
        this.nameTarget.textContent = file
            ? file.name
            : (this.emptyLabelValue || 'Glissez-déposez votre fichier ici ou cliquez pour le sélectionner');
    }

    updatePreview() {
        if (!this.hasPreviewTarget || !this.hasPreviewImageTarget) {
            return;
        }

        const file = this.inputTarget.files?.[0];
        if (!file || !file.type.startsWith('image/')) {
            this.previewTarget.classList.add('hidden');
            this.previewImageTarget.removeAttribute('src');
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            this.previewImageTarget.src = typeof reader.result === 'string' ? reader.result : '';
            this.previewTarget.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}
