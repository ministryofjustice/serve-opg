import dropzoneJS from "./Components/dropzoneJS";
import forms from "./Components/forms";
import { initAll } from "govuk-frontend";
import InlineUpload from "./Components/inlineUpload";
import PostcodeLookup from "./Components/postcodeLookup";
import SessionTimeoutDialog from "./Components/sessionTimeoutDialog";

const $ = require('jquery');

window.addEventListener('DOMContentLoaded', () => {
    initAll();

    // postcode lookup
    const postcodeLookupElt = document.querySelector('.js-PostcodeLookup')

    if (postcodeLookupElt !== null) {
        let postcodeLookup = new PostcodeLookup();
        postcodeLookup.cacheEls(postcodeLookupElt);
        postcodeLookup.bindEvents();
    }

    // drag and drop files zone
    const dropzoneEltId = 'div#court-order';

    if (document.querySelector(dropzoneEltId) !== null) {
        dropzoneJS.setup(
            dropzoneEltId,
            `/order/{orderId}/process-order-doc`,
            1,
            'court-order',
            'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,application/pdf',
            `/order/{orderId}/document/{documentId}`
        );
    }

    // inline upload; this has to happen after the above dropzone setup
    const documentMandatoryElt = document.querySelector('#documents-mandatory');

    if (documentMandatoryElt !== null) {
        let inlineUpload = new InlineUpload();
        inlineUpload.cacheEls(documentMandatoryElt);
        inlineUpload.init();
    }

    // forms
    forms.init('continue');

    // disable double clicks on buttons
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', function () {
            form.querySelectorAll('button.prevent-double-click').forEach((button) => {
                button.setAttribute('disabled', 'disabled');
            });
        });
    });

    // session timeout
    let overlay = document.querySelector('.session-timeout-underlay');

    if (overlay !== null) {
        let sessionExpires = overlay.dataset.sessionExpires,
            sessionShowPopupMs = overlay.dataset.sessionPopupShowAfter,
            keepAliveUrl = overlay.dataset.keepAliveUrl;

        new SessionTimeoutDialog({
            'element': $('#timeoutPopup'),
            'sessionExpiresMs': sessionExpires * 1000,
            'sessionPopupShowAfterMs': sessionShowPopupMs * 1000,
            'keepSessionAliveUrl': keepAliveUrl
        }).startCountdown();
    }
});
