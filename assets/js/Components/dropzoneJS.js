import Dropzone from 'dropzone/dist/min/dropzone.min';
Dropzone.autoDiscover = false;

class DropzoneJS {
    static setup(elementID, targetURL, maxFiles, fileIdentifier, acceptedTypes) {
        const previewTemplate = document.getElementById('dropzone__template__file').innerHTML;

        let dz =  new Dropzone(elementID, {
            url: targetURL,
            // Having to hack around what, appears to be, a bug with setting maxFiles to 1 preventing the use
            // of maxfilesexceeded event:
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
            maxFiles: maxFiles + 1,
            dictMaxFilesExceeded: `Only ${maxFiles} document(s) can be uploaded`,
            paramName: fileIdentifier,
            acceptedFiles: acceptedTypes,
            autoDiscover: false,
            createImageThumbnails: false,
            previewTemplate: previewTemplate,
            addRemoveLinks: true,
        });

        dz.on("success", () => {
            const event = new CustomEvent(
                'validDoc',
                {
                    detail: { valid: true }
                }
            );
            document.dispatchEvent(event);
            // reset maxFiles - see above in instantiating step for background
            dz.options.maxFiles = maxFiles;
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
