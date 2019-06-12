import organisation from '../Components/organisation'
import postcode from '../Components/postcodeLookup'

// ====================================================================================
// INITITALISE ALL MODULES

$(document).ready(function() {

    // organisation();
    // postcode();

    // Organisation
    // Note: This has been removed for now. Leave in as might be used later.
    // new GOVUK.displayOrg();

    new GOVUK.PostcodeLookup();
    new GOVUK.InlineUpload();
});
