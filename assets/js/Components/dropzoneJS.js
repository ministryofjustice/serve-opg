import Dropzone from 'dropzone/dist/min/dropzone.min';

class DropzoneJS{
    static setup(elementID, targetURL, maxFiles, fileIdentifier) {
        return new Dropzone(elementID, {
            url: targetURL,
            maxFiles: maxFiles,
            dictMaxFilesExceeded: `Only ${maxFiles} document(s) can be uploaded`,
            paramName: fileIdentifier,
            acceptedFiles: 'image/jpeg,image/png,image/tiff,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            init: () => {
                // dz emits accepted when a file is the correct type and has been POSTed to the value defined in url
                this.on("accepted", function() {
                    const event = new CustomEvent(
                        'validDoc',
                        {
                            detail: { valid: true }
                        }
                    );
                    document.dispatchEvent(event);
                });
            }
        })
    }
}

export default DropzoneJS
