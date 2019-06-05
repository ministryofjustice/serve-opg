import SessionTimeoutDialog from '../Components/sessionTimeoutDialog'

// instantiate object and attach events on page load
$().ready(function () { // $DOM READY EVENT

    let overlay = document.querySelector('.session-timeout-underlay'),
    sessionExpires = overlay.dataset.sessionExpires,
    sessionShowPopupMs = overlay.dataset.sessionPopupShowAfter,
    keepAliveUrl = overlay.dataset.keepAliveUrl;

    new SessionTimeoutDialog({
        'element': $('#timeoutPopup'),
        'sessionExpiresMs': sessionExpires * 1000,
        'sessionPopupShowAfterMs': sessionShowPopupMs * 1000,
        'keepSessionAliveUrl': keepAliveUrl
    }).startCountdown();
});