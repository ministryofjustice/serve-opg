import Dropzone from 'dropzone/dist/min/dropzone.min';
Dropzone.autoDiscover = false;

class DropzoneJS {
     static setup(elementID, targetURL, maxFiles, fileIdentifier, acceptedTypes, removeUrl) {
        let previewTemplate = document.getElementById('dropzone__template__file').innerHTML;

        let dz = new Dropzone(elementID, {
            url: targetURL,
            removeUrl: removeUrl,
            maxFilesCustom: maxFiles,
            paramName: fileIdentifier,
            acceptedFiles: acceptedTypes,
            autoDiscover: false,
            createImageThumbnails: false,
            previewTemplate: previewTemplate,
            addRemoveLinks: true,
            orderId: null
        });

         dz.options.orderId = document.querySelector(elementID).dataset.orderId;
         dz.options.url = dz.options.url.replace('{orderId}', dz.options.orderId);
         dz.options.removeUrl = dz.options.removeUrl.replace('{orderId}', dz.options.orderId);

        dz.on("addedfile", (file) => {
            if (dz.options.acceptedFiles.includes(file.type)){
                if (dz.files.length > dz.options.maxFilesCustom) {
                    file.fileLimitExceeded = true;
                    const fileToRemove = dz.files[0];
                    dz.removeFile(fileToRemove);
                }

                const event = new CustomEvent(
                    'validDoc',
                    {
                        detail: { valid: true }
                    }
                );
                document.dispatchEvent(event);
            } else {
                let errorDiv = document.querySelector('.dz-error-message ');
                errorDiv.innerText = dz.options.dictInvalidFileType;
                errorDiv.hidden = false;
            }
        });

        dz.on("success", (file, response) => {
            dz.options.removeUrl = dz.options.removeUrl.replace('{documentId}', response.documentId);

            const event = new CustomEvent(
                'orderDocProcessed',
            );
            document.dispatchEvent(event);

            let removeElement = document.querySelector('.dz-remove');
            if (removeElement !== null) {
                removeElement.classList.add('dropzone__file-remove');
                document.querySelector('.dz-filename').append(removeElement);
            }

            if (response.partial === true) {
                let form = document.querySelector('#continue-form');
                form.action = form.action.replace('summary', 'confirm-order-details');
            }
        });

        dz.on('removedfile', async function(file) {
            if (dz.files.length > dz.options.maxFilesCustom) {
                file.fileLimitExceeded = true;
            }

            if (dz.options.removeUrl.includes('{documentId}')) {
                return
            }

            const response = await fetch(dz.options.removeUrl, {
                method: 'DELETE',
            });

            await response.json()
            .then(response => {
                if (response.success !== 1) {
                    console.log('Error removing file from S3: ' + response.error);
                }
            });

            const event = new CustomEvent(
                'docRemoved',
                {
                    detail: { fileLimitExceeded: file.fileLimitExceeded }
                }
            );
            document.dispatchEvent(event);
        });

        dz.on('error', (file, errorMessage) => {
            if (errorMessage.includes('The case number in the document does not match the case number for this order. Please check the file and try again.')) {
                let removeElement = document.querySelector('.dz-remove');
                removeElement.classList.add('dropzone__file-remove');
                document.querySelector('.dz-filename').append(removeElement);

                const event = new CustomEvent('wrongCaseNumber');
                document.dispatchEvent(event);
            }
        });

        return dz;
    }
}

export default DropzoneJS
