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

describe('instantiating Dropzone', () => {
   describe('sets', () => {
       document.body.innerHTML =
           '<div id="court-order">' +
           '</div>'                         +
           '<div id="error" hidden="true">' +
           '</div>';
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
          document.body.innerHTML =
              '<div id="court-order">' +
              '</div>'                         +
              '<div id="error" hidden="true">' +
              '</div>';
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
          document.body.innerHTML =
              '<div id="court-order">' +
              '</div>'                         +
              '<div id="error" hidden="true">' +
              '</div>';
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
          document.body.innerHTML =
              '<div id="court-order">' +
              '</div>'                         +
              '<div id="error" hidden="true">' +
              '</div>';
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
           document.body.innerHTML =
               '<div id="court-order">' +
               '</div>'                         +
               '<div id="error" hidden="true">' +
               '</div>';
           let element = document.getElementById("court-order");
           let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

           const spy = jest.spyOn(dz, 'removeFile');

           let mockFile = getMockFile();
           dz.addFile(mockFile);
           dz.addFile(mockFile);

           expect(spy).toHaveBeenCalledTimes(1);
           expect(spy).toHaveBeenCalledWith(mockFile);
       });

      it('alerts user that max file limit has been reached', () => {
          document.body.innerHTML =
              '<div id="court-order">' +
              '</div>'                         +
              '<div id="error" hidden="true">' +
              '</div>';
          let element = document.getElementById("court-order");
          let dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court-order');

          dz.addFile(getMockFile());

          const errorDiv = document.getElementById('error');

          expect(errorDiv.hidden).toBe(false);
          expect(errorDiv.innerText).toContain('Only 1 document(s) can be uploaded');
      });
   })
});
