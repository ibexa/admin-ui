(function(global, doc) {
    const passwordInputNode = doc.querySelector('.ibexa-login__input--password');
    const viewIconNode = doc.querySelector('.ibexa-login__password-visibility-toggler .ibexa-icon--view');
    const viewHideIconNode = doc.querySelector('.ibexa-login__password-visibility-toggler .ibexa-icon--view-hide');
    const loginBtn = doc.querySelector('.ibexa-login__btn--sign-in');
    const nameInput = doc.querySelector('.ibexa-login__input--name');
    const passwordInput = doc.querySelector('.ibexa-login__input--password');
    const toggleLoginBtnState = () => {
        const shouldBeDisabled = !nameInput.value || !passwordInput.value;

        loginBtn.toggleAttribute('disabled', shouldBeDisabled);
    };

    doc.querySelector('.ibexa-login__password-visibility-toggler').addEventListener('click', (event) => {
        if (passwordInputNode) {
            const inputTypeToSet = passwordInputNode.type === 'password' ? 'text' : 'password';

            passwordInputNode.type = inputTypeToSet;
            viewIconNode.classList.toggle('d-none');
            viewHideIconNode.classList.toggle('d-none');
        }
    });

    if (loginBtn) {
        nameInput.addEventListener('keyup', toggleLoginBtnState, false);
        passwordInput.addEventListener('keyup', toggleLoginBtnState, false);

        toggleLoginBtnState();
    }
})(window, window.document);
