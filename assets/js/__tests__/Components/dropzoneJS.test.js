import DropzoneJS from '../../Components/dropzoneJS';
import Dropzone from 'dropzone/dist/min/dropzone.min';

let getMockFile = () =>
    ({
       status: Dropzone.ADDED,
       accepted: true,
       name: "test file name",
       size: 123456,
       type: "image/jpeg",
       upload: {
          filename: "test file name"
       }
    });

let previewFileHTML = `
<div id="dropzone__template__file">
    <div class="dz-preview dz-file-preview">
        <div class="dz-details govuk-body">
            <div class="dz-filename"><span data-dz-name=""></span> <span class="dz-size" data-dz-size=""></span></div>
            <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress=""></span></div>
            <div class="dz-error-message govuk-error-message"><span data-dz-errormessage=""></span></div>
        </div>
    </div> 
</div> 
`;

let setDocumentBody = () => (
    document.body.innerHTML = `
        <div id="court-order"></div>
        ${previewFileHTML}
    `
);

describe('dropzoneJS', () => {
    describe('instantiating Dropzone', () => {
        describe('sets', () => {
            setDocumentBody();

            let element = document.getElementById("court-order");
            let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

            it('paramName', () => {
                expect(dz.options.paramName).toBe('court-order');
            });

            it('url', () => {
                expect(dz.options.url).toBe('/orders/upload');
            });

            it('dictMaxFilesExceeded', () => {
                expect(dz.options.dictMaxFilesExceeded).toBe('Only 1 document(s) can be uploaded');
            });

            it('maxFiles', () => {
                expect(dz.options.maxFiles).toBe(1);
            });
        })
    });

    describe('adding a file', () => {
        describe('listed in acceptedFiles', () => {
            it('should be accepted', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

                const acceptedTypes = ['image/jpeg', 'image/png', 'image/tiff', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                let mockFile = getMockFile();

                acceptedTypes.forEach((type) => {
                    mockFile.type = type;
                    dz.accept(mockFile, err => expect(err).not.toBeDefined());
                });
            });

            it('should dispatch a validFile event', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

                const spy = jest.spyOn(document, 'dispatchEvent');

                const expectedEvent = new CustomEvent(
                    'validDoc',
                    {
                        detail: { valid: true }
                    }
                );

                const mockFile = getMockFile();
                dz.addFile(mockFile);

                expect(spy).toHaveBeenCalledTimes(1);
                expect(spy).toHaveBeenCalledWith(expectedEvent);
            })
        });

        describe('not listed in acceptedFiles', () => {
            it('should be rejected', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

                const nonAcceptedTypes = ['text/css', 'text/csv', 'image/bmp', 'image/gif', 'text/javascript', 'application/zip'];
                nonAcceptedTypes.forEach((type) => {
                    dz.accept({ type: type }, err => expect(err).toBeDefined());
                });
            });
        });

        describe('maxfilesexceeded event', () => {
            it('removes the last added file', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

                const spy = jest.spyOn(dz, 'removeFile');

                let mockFile = getMockFile();
                dz.files.push(mockFile);
                dz.addFile(mockFile);

                expect(spy).toHaveBeenCalledTimes(1);
                expect(spy).toHaveBeenCalledWith(mockFile);
            });

            it('alerts user that max file limit has been reached', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

                let mockFile = getMockFile();
                dz.addFile(mockFile);

                const errorDiv = document.querySelector('.dz-error-message');

                expect(errorDiv.innerHTML).toContain('Only 1 document(s) can be uploaded');
            });
        })
    });
});
