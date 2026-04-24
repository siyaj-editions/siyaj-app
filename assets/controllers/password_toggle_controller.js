import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['input', 'label', 'showIcon', 'hideIcon'];

    connect() {
        this.update();
    }

    toggle() {
        const nextType = this.inputTarget.type === 'password' ? 'text' : 'password';
        this.inputTarget.type = nextType;
        this.update();
    }

    update() {
        const isVisible = this.inputTarget.type === 'text';

        if (this.hasLabelTarget) {
            this.labelTarget.setAttribute('aria-label', isVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
        }

        if (this.hasShowIconTarget) {
            this.showIconTarget.classList.toggle('hidden', isVisible);
        }

        if (this.hasHideIconTarget) {
            this.hideIconTarget.classList.toggle('hidden', !isVisible);
        }
    }
}
