import { Controller } from '@hotwired/stimulus';

const COOKIE_NAME = 'siyaj_cookie_consent';
const COOKIE_MAX_AGE = 60 * 60 * 24 * 180;

export default class extends Controller {
    static targets = ['banner', 'acceptButton', 'declineButton'];

    connect() {
        const consent = this.readConsent();

        if (consent === 'accepted' || consent === 'declined') {
            this.applyConsent(consent, false);
            return;
        }

        this.showBanner();
    }

    accept() {
        this.applyConsent('accepted', true);
    }

    decline() {
        this.applyConsent('declined', true);
    }

    applyConsent(state, persist) {
        if (persist) {
            this.writeConsent(state);
        }

        document.documentElement.dataset.cookieConsent = state;
        window.SIYAJ_COOKIE_CONSENT = state;
        window.dispatchEvent(new CustomEvent('siyaj:cookie-consent-changed', {
            detail: { consent: state },
        }));

        this.hideBanner();
    }

    showBanner() {
        this.bannerTarget.classList.remove('hidden');
        this.bannerTarget.classList.add('flex');
    }

    hideBanner() {
        this.bannerTarget.classList.remove('flex');
        this.bannerTarget.classList.add('hidden');
    }

    readConsent() {
        const cookie = document.cookie
            .split('; ')
            .find((entry) => entry.startsWith(`${COOKIE_NAME}=`));

        return cookie ? decodeURIComponent(cookie.split('=').slice(1).join('=')) : null;
    }

    writeConsent(state) {
        const secure = window.location.protocol === 'https:' ? '; secure' : '';
        document.cookie = `${COOKIE_NAME}=${encodeURIComponent(state)}; path=/; max-age=${COOKIE_MAX_AGE}; samesite=lax${secure}`;
    }
}
