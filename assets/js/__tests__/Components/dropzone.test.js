import DropzoneJS from '../../Components/dropzoneJS';

describe('instantiating Dropzone', () => {
   describe('sets', () => {
      let element = document.createElement("court-order");
      const dz = DropzoneJS.setup(element, '/orders/upload', 1, 'court_order');

      it('paramName', () => {
         expect(dz.options.paramName).toBe('court_order');
      })

      it('url', () => {
         expect(dz.options.url).toBe('/orders/upload');
      })

      it('dictMaxFilesExceeded', () => {
         expect(dz.options.dictMaxFilesExceeded).toBe('Only 1 document(s) can be uploaded');
      })

      it('maxFiles', () => {
         expect(dz.options.maxFiles).toBe(1);
      })
   })
});