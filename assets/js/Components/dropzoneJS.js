import Dropzone from 'dropzone/dist/min/dropzone.min';
Dropzone.autoDiscover = false;

class DropzoneJS {
    static setup(elementID, targetURL, maxFiles, fileIdentifier, acceptedTypes) {
        const previewTemplate = document.getElementById('dropzone__template__file').innerHTML;

        let dz =  new Dropzone(elementID, {
            url: targetURL,
            maxFiles: maxFiles,
            dictMaxFilesExceeded: `Only ${maxFiles} document(s) can be uploaded`,
            paramName: fileIdentifier,
            acceptedFiles: acceptedTypes,
            autoDiscover: false,
            createImageThumbnails: false,
            previewTemplate: previewTemplate,
            addRemoveLinks: true,
        });

        dz.on("addedfile", (file) => {
            if (dz.options.acceptedFiles.includes(file.type)){
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
            const event = new CustomEvent(
                'orderDocProcessed',
            );
            document.dispatchEvent(event);
        });

        dz.on('removedfile', (file) => {
            const event = new CustomEvent(
                'docRemoved',
                {
                    detail: { fileLimitExceeded: file.fileLimitExceeded }
                }
            );
            document.dispatchEvent(event);
        });

        dz.on('maxfilesexceeded', (file) => {
            let errorDiv = document.querySelector('.dz-error-message ');
            errorDiv.innerText = dz.options.dictMaxFilesExceeded;
            errorDiv.hidden = false;
            file.fileLimitExceeded = true;
            dz.removeFile(file);
        });

        return dz;
    }
}

export default DropzoneJS
