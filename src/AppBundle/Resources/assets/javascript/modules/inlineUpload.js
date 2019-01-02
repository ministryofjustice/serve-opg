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
            dropZone: {
                url: '/upload-document',
                acceptedFiles: 'image/jpeg,image/png,image/tiff,application/pdf,.doc,.docx',
                addRemoveLinks: true,
                createImageThumbnails: false
            }
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

        processExistingDocuments: function (dropZone, existingDocuments) {
            for (var i = 0; i < existingDocuments.length; i++) {
                dropZone[0].dropzone.files.push(existingDocuments[i]);
                dropZone[0].dropzone.emit("addedfile", existingDocuments[i]);
                //dropZone[0].dropzone.emit("thumbnail", existingDocuments[i], "/image/url");
                dropZone[0].dropzone.emit("complete", existingDocuments[i]);

                // remove file size as unknown for existing documents
                var file = dropZone[0].dropzone.files.slice().pop();
                var $filePreviewTemplate = $(file.previewTemplate);
                $('.dz-size', $filePreviewTemplate).remove();
            }
        },

        setupDocumentMandatoryUpload: function(){
            var context = this;
            var dropZoneWrapper = $('<div/>', {
                'class': 'inline-upload__wrapper govuk-!-margin-bottom-6'
            });
            var dropZoneSettings = $.extend({}, context.settings.dropZone);

            context.$documentMandatory.addClass('govuk-visually-hidden');
            context.$documentMandatory.before(dropZoneWrapper);

            $('[data-doc-type]', context.$documentMandatory).each(function(){
                var docType = $(this).data('doc-type');
                var docTypeNiceName = $('td', this).eq(0).text();
                var documentName = $('td', this).eq(1).text();
                var dropZoneId = 'dropZone-' . docType;
                var dropZonePlaceholder = $('<div/>', {
                    'id': dropZoneId,
                    'class': 'dropzone inline-upload__placeholder inline-upload__placeholder--multiple text',
                    'data-inline-doc-type': docType
                });
                var dropZoneCopy = '<p class="dz-default dz-message">Drop <strong>' + docTypeNiceName + '</strong> document here or click to upload.</p>'
                var existingDocuments=[];

                // handle existing file uploads
                documentName = documentName.replace(/\n/g, " ");
                documentName = documentName.trim();
                if (documentName && documentName !== '-') {
                    existingDocuments.push(
                        { name: documentName },
                    );
                }

                dropZonePlaceholder.append(dropZoneCopy);

                dropZoneWrapper.append(dropZonePlaceholder);

                dropZoneSettings.maxFiles = 1;
                dropZoneSettings.init = function() {
                    // keep last file added
                    this.on('addedfile', function(file) {
                        if (this.files.length > 1) {
                            this.removeFile(this.files[0]);
                        }
                    });
                };

                var dropZone = dropZonePlaceholder.dropzone(dropZoneSettings);

                context.processExistingDocuments(dropZone, existingDocuments);

            });
        },

        setupDocumentOtherUpload: function(){
            var context = this;
            var dropZonePlaceholder = $('<div/>', {
                'class': 'dropzone inline-upload__placeholder inline-upload__placeholder--required text',
                'data-inline-doc-type': ''
            });
            var dropZoneCopy = '<p class="dz-default dz-message">Drop document here or click to upload.</p>'
            var existingDocuments=[];

            dropZonePlaceholder.append(dropZoneCopy);

            context.$documentOther.addClass('govuk-visually-hidden');
            context.$documentOther.before(dropZonePlaceholder);

            $('tbody tr td:nth-child(1)', context.$documentOther).each( function(){
                var documentName = $(this).text().replace(/\n/g, " ");
                documentName = documentName.trim();
                if (documentName && documentName !== '-') {
                    existingDocuments.push(
                        { name: documentName },
                    );
                }
            });

            var dropZone = dropZonePlaceholder.dropzone(context.settings.dropZone);

            context.processExistingDocuments(dropZone, existingDocuments);

        },


    }

    root.GOVUK.InlineUpload = InlineUpload;

}).call(this);
