import { startStimulusApp } from '@symfony/stimulus-bundle';
import CookieConsentController from './controllers/cookie_consent_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('cookie-consent', CookieConsentController);
