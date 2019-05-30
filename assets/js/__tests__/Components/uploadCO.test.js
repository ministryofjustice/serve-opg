import uploadCO from '../../Components/uploadCO';

describe('init', () => {
    it('adds a validDoc eventListener to continue button', () => {
        document.addEventListener = jest.fn();

        // Set up our document body
        document.body.innerHTML =
            '<div>' +
            '  <button id="continue" />' +
            '</div>';

        uploadCO.init();

        expect(document.addEventListener).toHaveBeenCalledTimes(1);
        expect(document.addEventListener).toHaveBeenCalledWith('receiveData', expect.any(Function));
    })
})