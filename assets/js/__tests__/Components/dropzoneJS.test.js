import DropzoneJS from '../../Components/dropzoneJS';
import Dropzone from 'dropzone/dist/min/dropzone.min';
const fetchMock = require('fetch-mock');
fetchMock.config.overwriteRoutes = true;

let getMockFile = (fileType='image/tiff') => {
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

let formHTML = `
<form action="/order/1/summary" id="continue-form">
    <button id="continue"></button>    
</form>
`;

let setDocumentBody = () => {
    document.body.innerHTML = `
        <div id="court-order" data-order-id="1"></div>
        ${previewFileHTML}
        ${formHTML}
    `;
}

describe('dropzoneJS', () => {
    describe('instantiating Dropzone', () => {
        describe('sets', () => {
            setDocumentBody();

            let element = document.getElementById("court-order");
            let dz = DropzoneJS.setup("div#court-order",
                '/order/{orderId}/process-order-doc',
                1,
                'court-order',
                'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                '/order/{orderId}/document/{documentId}',
            );

            it('paramName', () => {
                expect(dz.options.paramName).toBe('court-order');
            });

            it('url', () => {
                expect(dz.options.url).toBe('/order/1/process-order-doc');
            });

            it('removeUrl', () => {
                expect(dz.options.removeUrl).toBe('/order/1/document/{documentId}');
            });

            it('maxFilesCustom', () => {
                expect(dz.options.maxFilesCustom).toBe(1);
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

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
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

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
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

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
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

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
                );

                const nonAcceptedTypes = ['text/css', 'text/csv', 'image/bmp', 'image/gif', 'text/javascript', 'application/zip'];
                nonAcceptedTypes.forEach((type) => {
                    dz.accept({ type: type }, err => expect(err).toBeDefined());
                });
            });
        });

        describe('when maxFileCustom limit has been exceeded', () => {
            it('removes the last added file', () => {
                setDocumentBody();

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
                );

                const spy = jest.spyOn(dz, 'removeFile');

                fetchMock.delete('/order/1/document/2', { success: 1 });

                let mockFileWord = getMockFile('application/msword');
                let mockFileTiff = getMockFile('image/tiff');

                dz.files.push(mockFileWord);

                // Mocking the success event here due to quirks in dropzone execution order
                let responseText = {"success":true,"partial":true,"documentId":2};
                dz.emit("success", mockFileTiff, responseText);
                dz.addFile(mockFileTiff);

                expect(spy).toHaveBeenCalledTimes(1);
                expect(spy).toHaveBeenCalledWith(mockFileWord);
                expect(dz.files).not.toContain(mockFileWord);
                expect(dz.files).toContain(mockFileTiff);
            });
        });


        describe('error event', () => {
            describe('when error response contains case number mismatch', () => {
                it('shows an error message detailing the error', () => {
                    setDocumentBody();

                    let dz = DropzoneJS.setup("div#court-order",
                        '/order/{orderId}/process-order-doc',
                        1,
                        'court-order',
                        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        '/order/{orderId}/document/{documentId}',
                    );

                    fetchMock.delete('/order/1/document/2', { success: 1 });

                    let mockFile = getMockFile('application/msword');

                    dz.addFile(mockFile);
                    dz.emit("error", mockFile, 'The case number in the document does not match the case number for this order. Please check the file and try again.');

                    const dzElement = document.querySelector('.dz-error-message').innerHTML;
                    expect(dzElement).toEqual(expect.stringContaining('The case number in the document does not match the case number for this order. Please check the file and try again'));
                })
            })
        });

        describe('success event', () => {
            describe('response JSON contains partial:true', () => {
                it('amends the action of the continue button to be /order/{id}/confirm-order-details', () => {
                    setDocumentBody();

                    let dz = DropzoneJS.setup("div#court-order",
                        '/order/{orderId}/process-order-doc',
                        1,
                        'court-order',
                        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        '/order/{orderId}/document/{documentId}',
                    );

                    const mockFile = getMockFile('application/msword');
                    const responseText = {"success":true,"partial":true,"documentId":1};
                    dz.emit("success", mockFile, responseText);

                    let form = document.querySelector('#continue-form');
                    expect(form.action).toContain('/order/1/confirm-order-details');
                });

                it('replaces {documentId} of the removeUrl variable with documentId returned in response', () => {
                    setDocumentBody();

                    let dz = DropzoneJS.setup("div#court-order",
                        '/order/{orderId}/process-order-doc',
                        1,
                        'court-order',
                        'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        '/order/{orderId}/document/{documentId}',
                    );

                    const mockFile = getMockFile('application/msword');
                    const responseText = {"success":true,"partial":true,"documentId":3};
                    dz.emit("success", mockFile, responseText);

                    expect(dz.options.removeUrl).toBe('/order/1/document/3');
                });
            })
        });
    });

    describe('removing a file', () => {
        describe('equal or less than max file limit', () => {
            it('should dispatch a docRemoved event with fileLimitExceeded equal to false', () => {
                setDocumentBody();

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
                );

                const spy = jest.spyOn(document, 'dispatchEvent');

                const expectedEvent = new CustomEvent(
                    'docRemoved',
                    {
                        detail: {fileLimitExceeded: false}
                    }
                );

                fetchMock.delete('/order/1/document/3', { success: 1 });

                const mockFile = getMockFile('application/msword');

                dz.files.push(mockFile);
                const responseText = {"success":true,"partial":true,"documentId":3};
                dz.emit("success", mockFile, responseText);

                dz.removeFile(mockFile);

                expect(spy).toHaveBeenCalledWith(expectedEvent);
            });

            it('makes a DELETE request to removeUrl', () => {
                setDocumentBody();

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
                );

                fetchMock.delete('/order/1/document/3', { success: 1 });

                const mockFile = getMockFile('application/msword');

                dz.files.push(mockFile);
                const responseText = {"success":true,"partial":true,"documentId":3};
                dz.emit("success", mockFile, responseText);

                dz.removeFile(mockFile);

                expect(fetchMock.called('/order/1/document/3')).toBe(true)
            });

            it('does not attempt to DELETE files when removeUrl has not been updated with a valid documentId', () => {
                setDocumentBody();

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
                );

                fetchMock.reset();
                fetchMock.delete('/order/1/document/3', { success: 1 });

                const mockFile = getMockFile('application/msword');

                dz.files.push(mockFile);
                dz.removeFile(mockFile);

                expect(fetchMock.called('/order/1/document/3')).toBe(false)
            })

            it('console.logs when response from removeUrl endpoint is not successful', async () => {
                setDocumentBody();

                 let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
                );

                const spy = jest.spyOn(global.console, 'log');

                fetchMock.delete('/order/1/document/3', { success: 0, error: 'connection error' });

                const mockFile = getMockFile('application/msword');

                await dz.addFile(mockFile);
                const responseText = {"success":true,"partial":true,"documentId":3};
                dz.emit("success", mockFile, responseText);

                dz.removeFile(mockFile);

                // Add timeout here to give the async code time to process
                setTimeout(function() {
                        expect(spy).toHaveBeenCalledWith('Error removing file from S3: connection error');
                        return done();
                    }
                    , 10);

            })
        });

        describe('more than max file limit', () => {
            it('should dispatch a docRemoved event with fileLimitExceeded equal to true', () => {
                setDocumentBody();

                let dz = DropzoneJS.setup("div#court-order",
                    '/order/{orderId}/process-order-doc',
                    1,
                    'court-order',
                    'image/tiff,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    '/order/{orderId}/document/{documentId}',
                );

                const spy = jest.spyOn(document, 'dispatchEvent');

                const expectedEvent = new CustomEvent(
                    'docRemoved',
                    {
                        detail: {fileLimitExceeded: true}
                    }
                );

                fetchMock.delete('/order/1/document/3', { success: 1 });

                const mockFile = getMockFile('application/msword');

                dz.files.push(mockFile);
                dz.files.push(mockFile);
                const responseText = {"success":true,"partial":true,"documentId":3};
                dz.emit("success", mockFile, responseText);

                dz.removeFile(mockFile);

                expect(spy).toHaveBeenCalledWith(expectedEvent);
            });
        });
    });
});
