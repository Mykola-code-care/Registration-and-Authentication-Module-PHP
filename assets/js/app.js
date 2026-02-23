(function () {
    'use strict';

    // Password confirmation (register, reset-password, profile)
    function setupPasswordConfirm(formId, passId, confirmId, errorId, submitId) {
        var form = document.getElementById(formId);
        if (!form) return;
        var pass = document.getElementById(passId);
        var confirm = document.getElementById(confirmId);
        var err = document.getElementById(errorId);
        var submit = submitId ? document.getElementById(submitId) : form.querySelector('button[type="submit"]');

        function check() {
            if (!confirm || !confirm.value) {
                if (err) err.textContent = '';
                if (submit) submit.disabled = false;
                return true;
            }
            if (pass && pass.value !== confirm.value) {
                if (err) err.textContent = 'Passwords do not match';
                if (submit) submit.disabled = true;
                return false;
            }
            if (err) err.textContent = '';
            if (submit) submit.disabled = false;
            return true;
        }

        if (pass) pass.addEventListener('input', check);
        if (confirm) confirm.addEventListener('input', check);
        form.addEventListener('submit', function (e) {
            if (!check()) e.preventDefault();
        });
    }

    setupPasswordConfirm('register-form', 'password', 'password_confirm', 'password_confirm_error', 'register-submit');
    setupPasswordConfirm('reset-form', 'password', 'password_confirm', 'password_confirm_error', null);
    setupPasswordConfirm('profile-password-form', 'new_password', 'new_password_confirm', 'new_password_confirm_error', null);

    // Agreement modal
    var agreementLink = document.getElementById('agreement-link');
    var modal = document.getElementById('agreement-modal');
    if (agreementLink && modal) {
        var modalClose = modal.querySelector('.modal-close');
        var backdrop = modal.querySelector('.modal-backdrop');
        agreementLink.addEventListener('click', function (e) {
            e.preventDefault();
            modal.setAttribute('aria-hidden', 'false');
        });
        function closeModal() {
            modal.setAttribute('aria-hidden', 'true');
        }
        if (modalClose) modalClose.addEventListener('click', closeModal);
        if (backdrop) backdrop.addEventListener('click', closeModal);
    }

    // Confirm email modal (close button and backdrop)
    var confirmModal = document.getElementById('confirm-email-modal');
    if (confirmModal) {
        var confirmClose = confirmModal.querySelector('.modal-close');
        var confirmBackdrop = confirmModal.querySelector('.modal-backdrop');
        function closeConfirmModal() {
            confirmModal.setAttribute('aria-hidden', 'true');
        }
        if (confirmClose) confirmClose.addEventListener('click', closeConfirmModal);
        if (confirmBackdrop) confirmBackdrop.addEventListener('click', closeConfirmModal);
    }
})();
