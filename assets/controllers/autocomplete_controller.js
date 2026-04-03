import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'menu'];
    static values = {
        choices: Array,
        maxResults: { type: Number, default: 8 },
        openOnEmpty: { type: Boolean, default: true },
    };

    connect() {
        this.boundDocumentClick = this.handleDocumentClick.bind(this);
        document.addEventListener('click', this.boundDocumentClick);
    }

    disconnect() {
        document.removeEventListener('click', this.boundDocumentClick);
    }

    open() {
        this.render();
    }

    update() {
        this.render();
    }

    close() {
        window.setTimeout(() => this.hide(), 120);
    }

    select(event) {
        event.preventDefault();

        const option = event.target.closest('[data-autocomplete-option]');
        if (!option) {
            return;
        }

        this.inputTarget.value = option.dataset.autocompleteOption || '';
        this.hide();
        this.inputTarget.focus();
    }

    handleMouseDown(event) {
        event.preventDefault();
    }

    render() {
        if (!this.hasInputTarget || !this.hasMenuTarget) {
            return;
        }

        const term = this.normalize(this.inputTarget.value);
        const shouldFilter = term !== '' || !this.openOnEmptyValue;
        const choices = this.choicesValue
            .filter((choice) => !shouldFilter || this.normalize(choice).includes(term))
            .slice(0, this.maxResultsValue);

        if (choices.length === 0) {
            this.hide();
            return;
        }

        this.menuTarget.innerHTML = choices.map((choice) => `
            <button
                type="button"
                class="block w-full border-b border-black/5 px-4 py-3 text-left text-sm text-ink transition-colors duration-150 hover:bg-cream hover:text-gold last:border-b-0"
                data-autocomplete-option="${this.escapeAttribute(choice)}"
            >
                ${choice}
            </button>
        `).join('');

        this.menuTarget.classList.remove('hidden');
    }

    handleDocumentClick(event) {
        if (!this.element.contains(event.target)) {
            this.hide();
        }
    }

    hide() {
        if (!this.hasMenuTarget) {
            return;
        }

        this.menuTarget.innerHTML = '';
        this.menuTarget.classList.add('hidden');
    }

    normalize(value) {
        return (value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    escapeAttribute(value) {
        return String(value).replace(/"/g, '&quot;');
    }
}
