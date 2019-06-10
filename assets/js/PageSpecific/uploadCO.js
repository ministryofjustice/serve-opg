import dropzoneJS from '../Components/dropzoneJS'
import forms from '../Components/forms'

window.addEventListener('DOMContentLoaded', () => {
    dropzoneJS.setup('div#court-order',
        '/order/{orderId}/step-1-process',
        1,
        'court-order',
        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    );
    forms.init('continue');
});
