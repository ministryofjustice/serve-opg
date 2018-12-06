// Postcode lookup module

/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    // Define the class
    var PostcodeLookup = function (el) {
        console.log('PostcodeLookup');
        this.cacheEls('.js-PostcodeLookup');
        this.bindEvents();
        this.init();
    };

    PostcodeLookup.prototype = {
        settings: {
            postcodeSearchUrl: '/postcode-lookup',
            // used to populate fields
            // key is the key name sent in response and value is name of app's field
            fieldMappings: {
                line1: 'addressLine1',
                line2: 'addressLine2',
                line3: 'addressTown',
                postcode: 'addressPostcode'
            }
        },

        cacheEls: function (form) {
            this.$form = $(form);
            this.$addressWrapper = $('.address-wrapper', form);
            this.$postCodeSearchWrapper  = $('.postcode-lookup__search', form);
            this.$postCodeResultsWrapper  = $('postcode-lookup__results', form);
            this.$postCodeError  = $('postcode-lookup__error', form);
        },

        bindEvents: function () {
            this.$form.on('click.GOVUK.PostcodeLookup', '.js-PostcodeLookup__search-btn', this.searchClicked.bind(this));
        },

        init: function () {

        },

        searchClicked: function (e) {
            var $el = $(e.target);
            var $searchContainer = this.$postCodeSearchWrapper;
            var $postcodeLabel = $('label[for="postcode-lookup"]');

            // store the current query
            this.query = this.$form.find('.js-PostcodeLookup__query').val();

            if (!$el.hasClass('govuk-button--disabled')) {
                if (this.query !== '') {
                    this.findPostcode(this.query);
                    $el.addClass('govuk-button--disabled');
                    $postcodeLabel.children('.govuk-error-message').remove();
                } else {
                    $postcodeLabel.children('.govuk-error-message').remove();
                    $postcodeLabel
                        .append("<span class='govuk-error-message'>Please enter a postcode</span>");
                }
            }
            return false;
        },

        findPostcode: function (query) {
            $.ajax({
                url: this.settings.postcodeSearchUrl,
                data: {postcode: query},
                dataType: 'json',
                timeout: 10000,
                cache: true,
                error: this.postcodeError,
                success: this.postcodeSuccess
            });
        },

        postcodeError: function (jqXHR, textStatus, errorThrown) {
            var errorText = 'There was a problem: ';

            this.$form.find('.js-PostcodeLookup__search-btn').removeClass('govuk-button--disabled');

            if (textStatus === 'timeout') {
                errorText += 'the service did not respond in the allotted time';
            } else {
                errorText += errorThrown;
            }

            alert(errorText);
        },

        postcodeSuccess: function (response) {
            // not successful
            if (!response.success || response.addresses === null) {
                var $searchContainer = this.$wrap.find('.js-PostcodeLookup__search');
                var $postcodeLabel = $('label[for="postcode-lookup"]');

                if (response.isPostcodeValid) {
                    $searchContainer.addClass('error');
                    $postcodeLabel.children('.error-message').remove();
                    $postcodeLabel
                        .append($(this.errorMessageTpl({
                            'errorMessage': 'No address found for this postcode. Please try again or enter the address manually.'
                        })));
                } else {
                    alert('Enter a valid UK postcode');
                }
            } else {
                // successful

                if (this.$form.find('.js-PostcodeLookup__search-results').length > 0) {
                    this.$form.find('.js-PostcodeLookup__search-results').parent().replaceWith(this.resultTpl({results: response.addresses}));
                } else {
                    this.$form.find('.js-PostcodeLookup__search').after(this.resultTpl({results: response.addresses}));
                }
                this.$form.find('.js-PostcodeLookup__search-results').focus();
            }
            this.$form.find('.js-PostcodeLookup__search-btn').removeClass('govuk-button--disabled');
        }

    }



    root.GOVUK.PostcodeLookup = PostcodeLookup;

}).call(this);
