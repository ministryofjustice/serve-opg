/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.scss');
require('dropzone/dist/min/dropzone.min');

import { preventDoubleClick } from './Components/buttons';
import { initAll } from 'govuk-frontend'

initAll();

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = require('jquery');

// Example usage: Prevent double-click on all buttons with the class "prevent-double-click"
$(document).ready(function() {
    $('form').on('submit', function(e) {
        const button = $(this).find('.prevent-double-click');
        preventDoubleClick(button[0]);
    });
});
