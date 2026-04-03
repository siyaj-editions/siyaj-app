import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['list'];
    static values = {
        template: String,
        index: Number,
        itemSelector: { type: String, default: '[data-collection-item]' },
    };

    add(event) {
        event.preventDefault();

        const index = this.nextIndex();
        const html = this.templateValue.replace(/__name__/g, String(index));
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();

        const entry = wrapper.firstElementChild;
        if (!entry) {
            return;
        }

        this.listElement.appendChild(entry);
        this.indexValue = index + 1;
    }

    remove(event) {
        event.preventDefault();

        const entry = event.target.closest(this.itemSelectorValue);
        if (!entry) {
            return;
        }

        const entries = this.listElement.querySelectorAll(this.itemSelectorValue);
        if (entries.length === 1) {
            const input = entry.querySelector('input');
            if (input) {
                input.value = '';
            }
            return;
        }

        entry.remove();
    }

    nextIndex() {
        const indexedInputs = Array.from(this.listElement.querySelectorAll('input[name]'))
            .map((input) => {
                const match = input.name.match(/\[(\d+)\]$/);
                return match ? Number(match[1]) : -1;
            })
            .filter((value) => value >= 0);

        const highestExistingIndex = indexedInputs.length > 0 ? Math.max(...indexedInputs) + 1 : 0;

        return Math.max(highestExistingIndex, this.indexValue || 0);
    }

    get listElement() {
        return this.hasListTarget ? this.listTarget : this.element;
    }
}
