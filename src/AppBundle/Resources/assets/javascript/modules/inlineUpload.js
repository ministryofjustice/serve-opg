// Inline upload module

/*jshint browser: true */
(function () {
    "use strict";

    var root = this,
        $ = root.jQuery;

    if (typeof GOVUK === 'undefined') {
        root.GOVUK = {};
    }

    // Define the class
    var InlineUpload = function (el) {
        this.cacheEls();
        // this.bindEvents();
        this.init();
    };

    InlineUpload.prototype = {
        settings: {
            documentServiceUrl: '/upload-document'
        },

        cacheEls: function () {
            this.$documentMandatory = $('#documents-mandatory');
            this.$documentOther = $('#documents-other');
        },
        
        init: function () {
            Dropzone.autoDiscover = false;

            this.setupDocumentMandatoryUpload();
            this.setupDocumentOtherUpload();
        },

        setupDocumentMandatoryUpload: function(){
            var context = this;
            var dropZoneWrapper = $('<div/>', {
                'class': 'inline-upload__wrapper govuk-!-margin-bottom-6'
            });

            context.$documentMandatory.addClass('govuk-visually-hidden');
            context.$documentMandatory.before(dropZoneWrapper);

            $('[data-doc-type]', context.$documentMandatory).each(function(){
                var docType = $(this).data('doc-type');
                var dropZonePlaceholder = $('<div/>', {
                    'class': 'inline-upload__placeholder inline-upload__placeholder--multiple text',
                    'data-inline-doc-type': docType
                });
                var dropZoneCopy = '<p>Drop <strong>' + this.innerText + '</strong> document here or click to upload.</p>'

                dropZonePlaceholder.append(dropZoneCopy);

                dropZoneWrapper.append(dropZonePlaceholder);

                dropZonePlaceholder.dropzone({ url: context.settings.documentServiceUrl });

            });
        },

        setupDocumentOtherUpload: function(){
            var context = this;
            var dropZonePlaceholder = $('<div/>', {
                'class': 'inline-upload__placeholder inline-upload__placeholder--required text',
                'data-inline-doc-type': ''
            });
            var docType = $('[data-doc-type]', context.$documentOther).data('doc-type');
            var dropZoneCopy = '<p>Drop document here or click to upload.</p>'

            dropZonePlaceholder.append(dropZoneCopy);

            context.$documentOther.addClass('govuk-visually-hidden');
            context.$documentOther.before(dropZonePlaceholder);

            dropZonePlaceholder.dropzone({ url: context.settings.documentServiceUrl });

        },


    }

    root.GOVUK.InlineUpload = InlineUpload;

}).call(this);
