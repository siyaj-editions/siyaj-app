import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['tab', 'panel'];

    connect() {
        const firstKey = this.tabTargets[0]?.dataset.editorialTabsKeyParam;
        if (firstKey) {
            this.activate(firstKey);
        }
    }

    show(event) {
        this.activate(event.params.key);
    }

    activate(key) {
        this.tabTargets.forEach((tab) => {
            const active = tab.dataset.editorialTabsKeyParam === key;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-pressed', active ? 'true' : 'false');

            if (active) {
                this.element.style.setProperty('--editorial-active-index', tab.dataset.editorialTabsIndexParam ?? '0');
                const nav = tab.closest('.editorial-tabs-nav');
                if (nav) {
                    this.element.style.setProperty('--editorial-active-width', `${tab.offsetWidth}px`);
                    this.element.style.setProperty('--editorial-active-offset', `${tab.offsetLeft}px`);
                }
            }
        });

        this.panelTargets.forEach((panel) => {
            const active = panel.dataset.key === key;
            panel.classList.toggle('is-active', active);
            panel.hidden = !active;
        });
    }
}
