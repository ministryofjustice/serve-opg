class forms {
    static init(elementId) {
        document.addEventListener('validDoc', (e) => {
            if (e.detail.valid) {
                this.removeDisabledFrom(elementId)
            }
        });
    }

    static removeDisabledFrom(elementId) {
        let element = document.getElementById(elementId);
        element.removeAttribute('disabled')
    }
}

export default forms
