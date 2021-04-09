import PostcodeLookup from '../Components/postcodeLookup'

window.addEventListener('DOMContentLoaded', (event) => {
    let postcodeLookup = new PostcodeLookup();
    postcodeLookup.cacheEls('.js-PostcodeLookup');
    postcodeLookup.bindEvents();
});