import jquery from 'jquery'

const $ = jquery;

class PostcodeLookup {
    constructor() {
        this.settings = {
            postcodeSearchUrl: '/postcode-lookup',
                // used to populate fields
                // key is the key name sent in response and value is name of app's field
            fieldMappings: {
                line1: 'addressLine1',
                line2:'addressLine2',
                line3: 'addressTown',
                postcode: 'addressPostcode'
            }
        }
    }

    cacheEls (form) {
        this.$form = $(form);
        this.$addressWrapper = $('.address-wrapper', form);
        this.$postCodeResultsWrapper = $('.postcode-lookup__results', form);
        this.$postcodeLabel = $('label[for="postcode-lookup"]', form);
    }

    bindEvents() {
        this.$form.on('click.GOVUK.PostcodeLookup', '.js-PostcodeLookup__search-btn', this.searchClicked.bind(this));
        this.$form.on('click.GOVUK.PostcodeLookup', '.js-PostcodeLookup__toggle-address', this.addressToggleClicked.bind(this));
        this.$form.on('change.moj.Modules.PostcodeLookup', '.js-PostcodeLookup__search-results', this.resultsChanged.bind(this));
    }

    addressToggleClicked (e) {
        e.preventDefault();
        this.$addressWrapper.show();
    }

    searchClicked (e) {
        let $el = $(e.target);

        // store the current query
        this.query = this.$form.find('.js-PostcodeLookup__query').val();

        if (!$el.hasClass('govuk-button--disabled')) {
            if (this.query !== '') {
                this.findPostcode(this.query);
                $el.addClass('govuk-button--disabled');
                this.$postcodeLabel.children('.govuk-error-message').remove();
            } else {
                this.$postcodeLabel.children('.govuk-error-message').remove();
                this.$postcodeLabel
                    .append("<span class='govuk-error-message'>Please enter a postcode</span>");
            }
        }
        return false;
    }

    findPostcode (query) {
        $.ajax({
            url: this.settings.postcodeSearchUrl,
            data: {postcode: query},
            dataType: 'json',
            timeout: 10000,
            cache: true,
            error: this.postcodeError.bind(this),
            success: this.postcodeSuccess.bind(this)
        });
    }

    postcodeError (jqXHR, textStatus, errorThrown) {
        let errorText = 'There was a problem: ';

        this.$form.find('.js-PostcodeLookup__search-btn').removeClass('govuk-button--disabled');

        if (textStatus === 'timeout') {
            errorText += 'the service did not respond in the allotted time';
        } else {
            errorText += errorThrown;
        }

        alert(errorText);
    }

    postcodeSuccess (response) {
        // not successful
        if (!response.success || response.addresses === null) {
            let $searchContainer = this.$form.find('.js-PostcodeLookup__search');
            let $postcodeLabel = $('label[for="postcode-lookup"]');

            if (response.isPostcodeValid) {
                $searchContainer.addClass('error');
                $postcodeLabel.children('.error-message').remove();
                this.$postcodeLabel
                    .append("<span class='govuk-error-message'>No address found for this postcode. Please try again or enter the address manually.</span>");
            } else {
                this.$postcodeLabel
                    .append("<span class='govuk-error-message'>Please enter a a valid UK postcode</span>");
            }
        } else {
            // successful
            let $searchResultsSelect = this.$form.find('.js-PostcodeLookup__search-results');

            if ($searchResultsSelect.length > 0) {
                $searchResultsSelect.find('option').not(':first').remove();
                response.addresses.forEach(function (address, index) {
                    $searchResultsSelect.append('<option ' +
                        'value="' + index + '" ' +
                        'data-line1="' + address.addressLine1 + '" ' +
                        'data-line2="' + address.addressLine2 + '" ' +
                        'data-line3="' + address.addressTown + '" ' +
                        'data-postcode="' + address.addressPostcode + '">' + address.description
                        + '</option>');
                });
                $searchResultsSelect.focus();
                this.$postCodeResultsWrapper.removeClass('govuk-visually-hidden');
            }
        }
        this.$form.find('.js-PostcodeLookup__search-btn').removeClass('govuk-button--disabled');
    }

    resultsChanged (e) {
        let $el = $(e.target);

        let $selectedOption = $el.find(':selected');

        $('[name*="' + this.settings.fieldMappings.line1 + '"]').val($selectedOption.data('line1'));
        $('[name*="' + this.settings.fieldMappings.line2 + '"]').val($selectedOption.data('line2'));
        $('[name*="' + this.settings.fieldMappings.line3 + '"]').val($selectedOption.data('line3'));
        $('[name*="' + this.settings.fieldMappings.postcode + '"]').val($selectedOption.data('postcode')).change();

        this.$addressWrapper.show();
    }
}

export default PostcodeLookup;
