import dropzoneJS from '../Components/dropzoneJS'
import forms from '../Components/forms'

window.addEventListener('DOMContentLoaded', () => {
    const orderId = document.location.pathname.match("order\/(.*)\/upload")[1];

    dropzoneJS.setup('div#court-order',
        `/order/${orderId}/process-order-doc`,
        1,
        'court-order',
        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'continue'
    );
    forms.init('continue');
});
