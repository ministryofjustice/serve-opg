import forms from '../../Components/forms';

let setDocumentBody = (buttonDisabled) => {
    const button = buttonDisabled ? '<button id="continue" disabled />' : '<button id="continue"/>';

    document.body.innerHTML = `
        <div>
            ${button}
        </div>
    `
};

describe('forms', () => {
    describe('init', () => {
        it('adds required eventListeners to document', () => {
            setDocumentBody(true);

            const spy = jest.spyOn(document, 'addEventListener');

            forms.init('continue');

            expect(spy).toHaveBeenCalledTimes(3);
            expect(spy).toHaveBeenCalledWith('validDoc', expect.any(Function));
            expect(spy).toHaveBeenCalledWith('docRemoved', expect.any(Function));
            expect(spy).toHaveBeenCalledWith('wrongCaseNumber', expect.any(Function));
        });
    });

    describe('removeDisabledFrom', () => {
        it('removes disabled attribute from target element', () => {
            setDocumentBody(true);

            const button = document.getElementById('continue');

            forms.removeDisabledFrom('continue');

            expect(button.getAttributeNames()).not.toContain('disabled');
        })
    });

    describe('addDisabledTo', () => {
        it('adds disabled attribute to target element', () => {
            setDocumentBody(false);

            const button = document.getElementById('continue');

            forms.addDisabledTo('continue');

            expect(button.getAttributeNames()).toContain('disabled');
        })
    });

    describe('toggleDisabled', () => {
        describe('when target element is disabled', () => {
            it('should remove disabled attribute', () => {
                setDocumentBody(true);

                const button = document.getElementById('continue');

                forms.toggleDisabled('continue');

                expect(button.getAttributeNames()).not.toContain('disabled');
            })
        });

        describe('when target element is not disabled', () => {
            it('should add disabled attribute', () => {
                setDocumentBody(false);

                const button = document.getElementById('continue');

                forms.toggleDisabled('continue');

                expect(button.getAttributeNames()).toContain('disabled');
            })
        });
    })

    describe('continue button', () => {
        describe('is enabled by', () => {
            beforeEach(() => {
                setDocumentBody(true);
                forms.init('continue');
            });

            it('receiving a validDoc event', () => {
                const button = document.getElementById('continue');

                const event = new CustomEvent(
                    'validDoc',
                    {
                        detail: { valid: true }
                    }
                );

                document.dispatchEvent(event);

                expect(button.getAttributeNames()).not.toContain('disabled');
            });

            it('receiving a docRemoved event with fileLimitExceeded set to true', () => {
                const button = document.getElementById('continue');

                const event = new CustomEvent(
                    'docRemoved',
                    {
                        detail: {fileLimitExceeded: true}
                    }
                );

                document.dispatchEvent(event);

                expect(button.getAttributeNames()).not.toContain('disabled');
            });
        });

        describe('is disabled by', () => {
            beforeEach(() => {
                setDocumentBody(false);
                forms.init('continue');
            });

            it('receiving a docRemoved event with fileLimitExceeded set to false', () => {
                const button = document.getElementById('continue');

                const event = new CustomEvent(
                    'docRemoved',
                    {
                        detail: {fileLimitExceeded: false}
                    }
                );

                document.dispatchEvent(event);

                expect(button.getAttributeNames()).toContain('disabled');
            });
        });
    });
});

