import Dropzone from 'dropzone/dist/min/dropzone.min';

class DropzoneJS{
    static setup(elementID, targetURL, maxFiles, fileIdentifier) {
        return new Dropzone(elementID, {
            url: targetURL,
            maxFiles: maxFiles,
            dictMaxFilesExceeded: `Only ${maxFiles} document(s) can be uploaded`,
            paramName: fileIdentifier
        })
    }
}

export default DropzoneJS
