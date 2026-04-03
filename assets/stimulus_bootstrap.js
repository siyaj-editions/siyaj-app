import { startStimulusApp } from '@symfony/stimulus-bundle';
import AutocompleteController from './controllers/autocomplete_controller.js';
import CollectionController from './controllers/collection_controller.js';
import CookieConsentController from './controllers/cookie_consent_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('autocomplete', AutocompleteController);
app.register('collection', CollectionController);
app.register('cookie-consent', CookieConsentController);
