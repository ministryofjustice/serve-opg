class forms {
    static init(elementId) {
        document.addEventListener('validDoc', (e) => {
            if (e.detail.valid) {
                this.removeDisabledFrom(elementId);
            }
        });

        document.addEventListener('docRemoved', (e) => {
            if (e.detail.fileLimitExceeded) {
                this.removeDisabledFrom(elementId);
                return
            }
            this.addDisabledTo(elementId);
        });

        const checkBox = document.getElementById('cannot-find-checkbox');

        checkBox.addEventListener('click', (e) => {
            this.toggleDisabled(elementId);
        });
    }

    static removeDisabledFrom(elementId) {
        let element = document.getElementById(elementId);
        element.removeAttribute('disabled');
    }

    static addDisabledTo(elementId) {
        let element = document.getElementById(elementId);
        element.setAttribute('disabled', '');
    }

    static toggleDisabled(elementId) {
        let element = document.getElementById(elementId);
        element.disabled ?
            element.removeAttribute('disabled') : element.setAttribute('disabled', '');
    }
}

export default forms
