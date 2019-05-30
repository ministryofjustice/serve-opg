class uploadCO {
    static init() {
        document.addEventListener('receiveData', (e) => {
            if (event.target.matches('#continue')) {
                if (e.valid) {
                    button.removeAttribute('disabled')
                }
            }
        });
    }
}

export default uploadCO

