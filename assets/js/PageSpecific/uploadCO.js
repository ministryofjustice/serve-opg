import dropzoneJS from '../Components/dropzoneJS'
import forms from '../Components/forms'

dropzoneJS.setup('div#court-order', '/order/{orderId}/step-1-process', 1, 'court-order');
forms.init('continue');
