class uploadCO {
    static init() {
        document.addEventListener('validDoc', (e) => {
            if (e.detail.valid) {
                let button = document.getElementById('continue');
                button.removeAttribute('disabled')
            }
        });
    }
}

export default uploadCO
