import uploadCO from '../../Components/uploadCO';

describe('init', () => {
    it('adds a validDoc eventListener to continue button', () => {
        const spy = jest.spyOn(document, 'addEventListener');

        uploadCO.init('continue');

        expect(spy).toHaveBeenCalledTimes(1);
        expect(spy).toHaveBeenCalledWith('validDoc', expect.any(Function));
    });

    it('receiving a validDoc event enables continue button', () => {
        // Set up our document body
        document.body.innerHTML =
            '<div>' +
            '  <button id="continue" disabled="true" />' +
            '</div>';

        const button = document.getElementById('continue');

        uploadCO.init('continue');

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

describe('removeDisabledFrom', () => {
    it('removes disabled attribute from target element', () => {
        // Set up our document body
        document.body.innerHTML =
            '<div>' +
            '  <button id="continue" disabled="true" />' +
            '</div>';

        const button = document.getElementById('continue');

        uploadCO.removeDisabledFrom('continue');

        expect(button.getAttributeNames()).not.toContain('disabled');
    })
});
