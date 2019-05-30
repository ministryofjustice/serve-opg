import DropzoneJS from '../../Components/dropzoneJS';

describe('instantiating Dropzone', () => {
   describe('sets', () => {
      let element = document.createElement("court-order");
      const dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court_order');

      it('paramName', () => {
         expect(dz.options.paramName).toBe('court_order');
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
   let element = document.createElement("court-order");
   const dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court_order');

   describe('listed in acceptedFiles', () => {
      it('should be accepted', () => {
         const acceptedTypes = ['image/jpeg', 'image/png', 'image/tiff', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
         acceptedTypes.forEach((type) => {
            dz.accept({ type: type }, err => expect(err).not.toBeDefined());
         });
      });

      it('should dispatch a validFile event', () => {
         document.dispatchEvent = jest.fn();
         const event = new CustomEvent(
             'validDoc',
             {
                detail: { valid: true }
             }
         );

         dz.accept({ type: 'image/jpeg' }, err => err);

         expect(document.dispatchEvent).toHaveBeenCalledTimes(1);
         expect(document.dispatchEvent).toHaveBeenCalledWith(event);
      })
   });

   describe('not listed in acceptedFiles', () => {
      it('should be rejected', () => {
         const nonAcceptedTypes = ['text/css', 'text/csv', 'image/bmp', 'image/gif', 'text/javascript', 'application/zip'];
         nonAcceptedTypes.forEach((type) => {
            dz.accept({ type: type }, err => expect(err).toBeDefined());
         });
      });
   });
});
