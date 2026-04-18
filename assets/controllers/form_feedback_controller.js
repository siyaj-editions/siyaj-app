import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.fields = Array.from(this.element.querySelectorAll('input, textarea, select'));
        this.fields.forEach((field) => {
            field.addEventListener('invalid', this.handleInvalid, true);
            field.addEventListener('input', this.refreshField);
            field.addEventListener('change', this.refreshField);
        });

        this.element.addEventListener('submit', this.handleSubmit);
    }

    disconnect() {
        this.fields.forEach((field) => {
            field.removeEventListener('invalid', this.handleInvalid, true);
            field.removeEventListener('input', this.refreshField);
            field.removeEventListener('change', this.refreshField);
        });

        this.element.removeEventListener('submit', this.handleSubmit);
    }

    handleSubmit = () => {
        this.element.classList.add('form-attempted');
        this.fields.forEach((field) => this.toggleFieldState(field));
    };

    handleInvalid = (event) => {
        this.element.classList.add('form-attempted');
        this.toggleFieldState(event.target);
    };

    refreshField = (event) => {
        if (!this.element.classList.contains('form-attempted')) {
            return;
        }

        this.toggleFieldState(event.target);
    };

    toggleFieldState(field) {
        const invalid = !field.validity.valid;
        field.classList.toggle('is-invalid', invalid);
        field.classList.toggle('is-valid', !invalid);
    }
}
