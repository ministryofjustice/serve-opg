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

        var deputyType = $('#deputy_form_deputyType').val();
        setVisibility(deputyType);

        $('#deputy_form_deputyType').on('change', function(){
            var newDeputyType = $(this).val();
            setVisibility(newDeputyType);
        });
    };

    var setVisibility = function(deputyType){
        if (deputyType == 'lay') {
            $('#form-group-organisationName').addClass('govuk-visually-hidden');
        } else {
            $('#form-group-organisationName').removeClass('govuk-visually-hidden');
        }
    }

    root.GOVUK.displayOrg = displayOrg;

}).call(this);