import { startStimulusApp } from '@symfony/stimulus-bundle';
import AutocompleteController from './controllers/autocomplete_controller.js';
import CollectionController from './controllers/collection_controller.js';
import CookieConsentController from './controllers/cookie_consent_controller.js';
import DatePickerController from './controllers/date_picker_controller.js';
import EditorialTabsController from './controllers/editorial_tabs_controller.js';
import FormFeedbackController from './controllers/form_feedback_controller.js';
import UploadDropzoneController from './controllers/upload_dropzone_controller.js';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
app.register('autocomplete', AutocompleteController);
app.register('collection', CollectionController);
app.register('cookie-consent', CookieConsentController);
app.register('date-picker', DatePickerController);
app.register('editorial-tabs', EditorialTabsController);
app.register('form-feedback', FormFeedbackController);
app.register('upload-dropzone', UploadDropzoneController);
