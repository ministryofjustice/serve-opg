import Dropzone from 'dropzone/dist/min/dropzone.min';
Dropzone.autoDiscover = false;

class DropzoneJS {
    static setup(elementID, targetURL, maxFiles, fileIdentifier) {
        const previewTemplate = document.getElementById('dropzone__template__file').innerHTML;

        let dz =  new Dropzone(elementID, {
            url: targetURL,
            maxFiles: maxFiles,
            dictMaxFilesExceeded: `Only ${maxFiles} document(s) can be uploaded`,
            paramName: fileIdentifier,
            acceptedFiles: 'image/jpeg,image/png,image/tiff,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            autoDiscover: false,
            createImageThumbnails: false,
            previewTemplate: previewTemplate
        });

        dz.on("success", function() {
            if (dz.files.length > dz.options.maxFiles) {
                let errorDiv = document.querySelector('.dz-error-message ');
                errorDiv.innerText = dz.options.dictMaxFilesExceeded;
                errorDiv.hidden = false;
                dz.removeFile(file);
                return;
            }

            const event = new CustomEvent(
                'validDoc',
                {
                    detail: { valid: true }
                }
            );
            document.dispatchEvent(event);
        });

        dz.on('maxfilesexceeded', (file) => {
            // Having to hack around what, appears to be, a bug with setting maxFiles to 1:
            //
            // # dropzone.js - accept() {
            // ...
            // else if (this.options.maxFiles != null && this.getAcceptedFiles().length >= this.options.maxFiles) {
            //         done(this.options.dictMaxFilesExceeded.replace("{{maxFiles}}", this.options.maxFiles));
            //         return this.emit("maxfilesexceeded", file);
            //       }
            // ...
            // }
            //
            // This checks for >= rather than > so will always emit maxfilesexceeded for maxFiles=1

            if (dz.files.length > dz.options.maxFiles) {
                let errorDiv = document.querySelector('.dz-error-message ');
                errorDiv.innerText = dz.options.dictMaxFilesExceeded;
                errorDiv.hidden = false;
                dz.removeFile(file);
                return;
            }

            const event = new CustomEvent(
                'validDoc',
                {
                    detail: { valid: true }
                }
            );
            document.dispatchEvent(event);
        });

        return dz;
    }
}

export default DropzoneJS
