class uploadCO {
    static init() {
        document.addEventListener('validDoc', (e) => {
            if (event.target.matches('#continue')) {
                if (e.valid) {
                    button.removeAttribute('disabled')
                }
            }
        });
    }
}

export default uploadCO

