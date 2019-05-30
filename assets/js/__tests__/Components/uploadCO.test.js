import uploadCO from '../../Components/uploadCO';

describe('init', () => {
    it('adds a validDoc eventListener to continue button', () => {
        document.addEventListener = jest.fn();

        uploadCO.init();

        expect(document.addEventListener).toHaveBeenCalledTimes(1);
        expect(document.addEventListener).toHaveBeenCalledWith('validDoc', expect.any(Function));
    });
});

describe('validDoc eventListener', () => {
    it('removes disabled attribute from target element', () => {
        // Set up our document body
        document.body.innerHTML =
            '<div>' +
            '  <button id="continue" disabled="true" />' +
            '</div>';

        const button = document.getElementById('continue');

        uploadCO.init();

        const event = new CustomEvent(
            'validDoc',
            {
                detail: { valid: true }
            }
        );

        document.dispatchEvent(event);

        expect(button.getAttributeNames()).not.toContain('disabled');
    })
});
