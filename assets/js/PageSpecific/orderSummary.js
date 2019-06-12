import InlineUpload from '../Components/inlineUpload'

window.addEventListener('DOMContentLoaded', (event) => {
    let inlineUpload = new InlineUpload();
    inlineUpload.cacheEls();
    inlineUpload.init();
});


