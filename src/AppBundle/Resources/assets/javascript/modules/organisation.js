// ORGANISATION
// Show and hide organisation field depending on Deputy type selection

/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    var displayOrg = function () {

        $('#deputy_form_deputyType').on('change', function(){
            console.log($(this).val());
        })
    };

    root.GOVUK.displayOrg = displayOrg;

}).call(this);