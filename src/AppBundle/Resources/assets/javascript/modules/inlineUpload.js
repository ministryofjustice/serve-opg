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
        this.init();
    };

    InlineUpload.prototype = {
        settings: {
            dropZone: {
                addRoute: '/order/{orderId}/document/{docType}',
                removeRoute: '/order/{orderId}/document/{id}',
                acceptedFiles: 'image/jpeg,image/png,image/tiff,application/pdf,.doc,.docx',
                createImageThumbnails: false,
                removeButton: '<button>Remove</button>'
            }
        },

        cacheEls: function () {
            this.$documentMandatory = $('#documents-mandatory');
            this.$documentOther = $('#documents-other');
        },

        init: function () {
            this.initDropZone();
            this.setupDocumentMandatoryUpload();
            this.setupDocumentOtherUpload();
        },

        initDropZone: function () {
            Dropzone.autoDiscover = false;
            this.settings.dropZone.previewTemplate = $('#inline_upload_template').html();
            this.settings.dropZone.readyToServe = this.readyToServe();
            this.settings.dropZone.handleRemoveAction = this.handleRemoveAction();
            this.settings.dropZone.init = function () {
                this.on('addedfile', function (file) {
                    var _this = this;
                    var maxFiles = this.options.maxFiles;
                    if (maxFiles !== null && this.files.length > maxFiles) {
                        this.removeFile(this.files[0]);
                    }
                    if (file.addRemoveButton) {
                        _this.options.handleRemoveAction(file, _this);
                    }

                });
                this.on('error', function(file, data) {
                    var _this = this;
                    var removeButton = Dropzone.createElement(_this.options.removeButton);

                    // Listen to the click event
                    removeButton.addEventListener("click", function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        _this.removeFile(file);
                    });
                }),
                this.on('success', function(file, data){
                    var _this = this;
                    file.id = data.id;
                    file.orderId = data.orderId;
                    _this.options.readyToServe(file, data);
                    _this.options.handleRemoveAction(file, _this);
                });
            }
        },

        handleRemoveAction: function () {
            return function (file, _this){

                // Create the remove button
                var removeButton =  Dropzone.createElement(_this.options.removeButton);

                // Listen to the click event
                removeButton.addEventListener("click", function (e) {
                    // Make sure the button click doesn't submit the form:
                    var button = $(this);
                    e.preventDefault();
                    e.stopPropagation();
                    button.hide();

                    $.ajax({
                        url: _this.options.removeRoute
                            .replace('{orderId}', file.orderId)
                            .replace('{id}', file.id)
                        ,
                        type: 'DELETE',
                        success: function (data) {
                            if (data.success) {
                                // Remove the file preview.
                                _this.removeFile(file);
                                _this.options.readyToServe(file, data);
                            } else {
                                button.show();
                                // TODO populate error overlay
                                console.log('Failed to remove document');
                            }
                        },
                        error: function (data) {
                            button.show();
                            console.log('Failed to remove document');
                        }
                    });
                });

                // Add the button to the file preview element.
                file.previewElement.appendChild(removeButton);
            }
        },

        readyToServe: function() {
            return function (file, data) {
                var $serveOrderNotice = $('#serve_order_notice');
                var $serveOrderButton = $('#serve_order_button_disabled');
                if (data.readyToServe) {
                    $serveOrderNotice.addClass('govuk-visually-hidden');
                    $serveOrderButton.removeAttr("disabled").click(function () {
                        window.location.href = $(this).data('path');
                    });
                }
                else {
                    $serveOrderNotice.removeClass('govuk-visually-hidden');
                    $serveOrderButton.prop('disabled', true);
                    $serveOrderButton.unbind();
                }
            }
        },

        processExistingDocument: function (dropZone, existingDocument) {
                existingDocument.addRemoveButton = true;
                dropZone[0].dropzone.files.push(existingDocument);
                dropZone[0].dropzone.emit("addedfile", existingDocument);
                //dropZone[0].dropzone.emit("thumbnail", existingDocument, "/image/url");
                dropZone[0].dropzone.emit("complete", existingDocument);
                // remove file size as unknown for existing documents
                var file = dropZone[0].dropzone.files.slice().pop();
                var $filePreviewTemplate = $(file.previewTemplate);
                $('.dz-size', $filePreviewTemplate).remove();
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
                var orderId = $(this).data('order-id');
                var docType = $(this).data('doc-type');
                var docId = $(this).data('doc-id');
                var docTypeNiceName = $('td', this).eq(0).text();
                var documentName = $('td', this).eq(1).text();
                var dropZoneId = 'dropZone-' . docType;
                var dropZonePlaceholder = $('<div/>', {
                    'id': dropZoneId,
                    'class': 'dropzone inline-upload__placeholder inline-upload__placeholder--multiple text',
                    'data-inline-doc-type': docType
                });
                var dropZoneCopy = '<p class="dz-default dz-message">Drop <strong>' + docTypeNiceName + '</strong> document here or click to upload.</p>'

                dropZonePlaceholder.append(dropZoneCopy);
                dropZoneWrapper.append(dropZonePlaceholder);
                dropZoneSettings.url = dropZoneSettings.addRoute
                    .replace('{orderId}', orderId)
                    .replace('{docType}', docType);
                dropZoneSettings.maxFiles = 1;

                var dropZone = dropZonePlaceholder.dropzone(dropZoneSettings);

                // handle existing file uploads
                documentName = documentName.replace(/\n/g, " ");
                documentName = documentName.trim();
                if (documentName && documentName !== '-') {
                    context.processExistingDocument(dropZone,
                        {
                            id: docId,
                            orderId: orderId,
                            name: documentName
                        },
                    );
                }
            });
        },

        setupDocumentOtherUpload: function(){
            var context = this;
            var orderId = $(context.$documentOther).data('order-id');
            var docType = $(context.$documentOther).data('doc-type');
            var dropZoneSettings = $.extend({}, context.settings.dropZone);
            var dropZonePlaceholder = $('<div/>', {
                'class': 'dropzone inline-upload__placeholder inline-upload__placeholder--required text',
                'data-inline-doc-type': ''
            });
            var dropZoneCopy = '<p class="dz-default dz-message">Drop document here or click to upload.</p>'

            dropZonePlaceholder.append(dropZoneCopy);
            context.$documentOther.addClass('govuk-visually-hidden');
            context.$documentOther.before(dropZonePlaceholder);
            dropZoneSettings.url = dropZoneSettings.addRoute
                .replace('{orderId}', orderId)
                .replace('{docType}', docType);

            var dropZone = dropZonePlaceholder.dropzone(dropZoneSettings);

            $('tbody tr', context.$documentOther).each( function(){
                var docId = $(this).data('doc-id');
                var documentName = $('td', this).eq(0).text().replace(/\n/g, " ");
                documentName = documentName.trim();
                if (documentName && documentName !== '-') {
                    context.processExistingDocument(dropZone,
                        {
                            id: docId,
                            orderId: orderId,
                            name: documentName
                        },
                    );
                }
            });
        },
    }

    root.GOVUK.InlineUpload = InlineUpload;

}).call(this);
