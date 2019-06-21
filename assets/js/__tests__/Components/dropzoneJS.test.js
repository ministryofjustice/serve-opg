import DropzoneJS from '../../Components/dropzoneJS';
import Dropzone from 'dropzone/dist/min/dropzone.min';

let getMockFile = (fileType='image/jpeg') => {
    return {
        status: Dropzone.ADDED,
        accepted: true,
        name: "test file name",
        size: 123456,
        type: fileType,
        upload: {
            filename: "test file name"
        }
    }
}

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

let setDocumentBody = () => {
    document.body.innerHTML = `
        <div id="court-order"></div>
        ${previewFileHTML}
        <button id="continue"></button>
    `;
}

describe('dropzoneJS', () => {
    describe('instantiating Dropzone', () => {
        describe('sets', () => {
            setDocumentBody();

            let element = document.getElementById("court-order");
            let dz = DropzoneJS.setup(element,
                '/orders/upload',
                1,
                'court-order',
                'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            );

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

            it('acceptedFiles', () => {
                expect(dz.options.acceptedFiles).toBe(
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                );
            });

            it('autoDiscover', () => {
                expect(dz.options.autoDiscover).toBe(false);
            });

            it('createImageThumbnails', () => {
                expect(dz.options.createImageThumbnails).toBe(false);
            });

            it('previewTemplate', () => {
                expect(dz.options.previewTemplate).toBe(document.getElementById('dropzone__template__file').innerHTML);
            });

            it('addRemoveLinks', () => {
                expect(dz.options.addRemoveLinks).toBe(true);
            });
        })
    });

    describe('adding a file', () => {
        describe('listed in acceptedFiles', () => {
            it('should be accepted', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element,
                    '/orders/upload',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                );

                const acceptedTypes = ['image/tiff', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                let mockFile = getMockFile();

                acceptedTypes.forEach((type) => {
                    mockFile.type = type;
                    dz.accept(mockFile, err => expect(err).not.toBeDefined());
                });
            });

            it('should dispatch a validFile event', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element,
                    '/orders/upload',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                );

                const spy = jest.spyOn(document, 'dispatchEvent');

                const expectedEvent = new CustomEvent(
                    'validDoc',
                    {
                        detail: { valid: true }
                    }
                );

                const mockFile = getMockFile('application/msword');
                dz.addFile(mockFile);

                // Add timeout here to give the queue time to process files
                setTimeout(function() {
                        expect(spy).toHaveBeenCalledTimes(1);
                        expect(spy).toHaveBeenCalledWith(expectedEvent);
                        return done();
                    }
                    , 10);
            });

            it('should append the remove element dz-filename element', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element,
                    '/orders/upload',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                );

                const mockFile = getMockFile('application/msword');
                dz.addFile(mockFile);

                const removeElement = '<a class="dz-remove dropzone__file-remove" href="javascript:undefined;" data-dz-remove="">Remove file</a>';

                // Add timeout here to give the queue time to process files
                setTimeout(function() {
                        let filenameElement = document.querySelector('.dz-filename');
                        expect(filenameElement).toContain(removeElement);
                        return done();
                    }
                    , 10);
            });
        });

        describe('not listed in acceptedFiles', () => {
            it('should be rejected', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element,
                    '/orders/upload',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                );

                const nonAcceptedTypes = ['text/css', 'text/csv', 'image/bmp', 'image/gif', 'text/javascript', 'application/zip'];
                nonAcceptedTypes.forEach((type) => {
                    dz.accept({ type: type }, err => expect(err).toBeDefined());
                });
            });
        });

        describe('maxfilesexceeded event', () => {
            describe('when maxfile limit has been exceeded', () => {
                it('removes the last added file', () => {
                    setDocumentBody();

                    let element = document.getElementById("court-order");
                    let dz = DropzoneJS.setup(element,
                        '/orders/upload',
                        1,
                        'court-order',
                        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    );

                    const spy = jest.spyOn(dz, 'removeFile');

                    let mockFile = getMockFile('application/msword');
                    dz.addFile(mockFile);
                    dz.addFile(mockFile);

                    expect(spy).toHaveBeenCalledTimes(1);
                    expect(spy).toHaveBeenCalledWith(mockFile);
                });

                it('alerts user that max file limit has been reached', () => {
                    setDocumentBody();

                    let element = document.getElementById("court-order");
                    let dz = DropzoneJS.setup(element,
                        '/orders/upload',
                        1,
                        'court-order',
                        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    );

                    let mockFile = getMockFile('application/msword');
                    dz.files.push(mockFile);
                    dz.addFile(mockFile);

                    const errorDiv = mockFile.previewElement.querySelector('.dz-error-message');

                    expect(errorDiv.innerText).toContain('Only 1 document(s) can be uploaded');
                });
            });
        });

        describe('error event', () => {
            describe('when error response contains case number mismatch', () => {
                it('shows an error message detailing the error', () => {
                    // @todo look into mocking xhr responses OR another way of testing this
                })
            })
        });

        describe('success event', () => {
            describe('response contains partial data extraction', () => {
                it('amends the action of the continue button to be /case/{id}/edit', () => {
                    setDocumentBody();

                    let element = document.getElementById("court-order");
                    let dz = DropzoneJS.setup(element,
                        '/orders/upload',
                        1,
                        'court-order',
                        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    );
                })
            })
        });
    });

    describe('removing a file', () => {
        describe('equal or less than max file limit', () => {
            it('should dispatch a docRemoved event with fileLimitExceeded equal to false', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element,
                    '/orders/upload',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                );

                const spy = jest.spyOn(document, 'dispatchEvent');

                const expectedEvent = new CustomEvent(
                    'docRemoved',
                    {
                        detail: {fileLimitExceeded: false}
                    }
                );

                const mockFile = getMockFile();
                dz.addFile(mockFile);
                dz.removeFile(mockFile);

                expect(spy).toHaveBeenCalledWith(expectedEvent);
            });
        });

        describe('more than max file limit', () => {
            it('should not dispatch a docRemoved event with fileLimitExceeded equal to true', () => {
                setDocumentBody();

                let element = document.getElementById("court-order");
                let dz = DropzoneJS.setup(element,
                    '/orders/upload',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'continue'
                );

                const spy = jest.spyOn(document, 'dispatchEvent');

                const expectedEvent = new CustomEvent(
                    'docRemoved',
                    {
                        detail: {fileLimitExceeded: true}
                    }
                );

                const mockFile = getMockFile();

                dz.addFile(mockFile);
                dz.addFile(mockFile);
                dz.removeFile(mockFile);

                expect(spy).toHaveBeenCalledWith(expectedEvent);
            });
        });
    });
});
