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
            postcodeSearchUrl: '/address-lookup',
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

            if (!$el.hasClass('disabled')) {
                if (this.query !== '') {
                    // $el.spinner(); --> // govuk-button--disabled
                    // this.findPostcode(this.query);
                    $postcodeLabel.children('.govuk-error-message').remove();
                } else {
                    $postcodeLabel.children('.govuk-error-message').remove();
                    $postcodeLabel
                        .append("<span class='govuk-error-message'>Please enter a postcode</span>");
                }
            }
            return false;
        }
    }



    root.GOVUK.PostcodeLookup = PostcodeLookup;

}).call(this);
