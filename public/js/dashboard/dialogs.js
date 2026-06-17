(() => {
    const U = window.DashboardUtils;
    if (!U) return;

    let dialogResolve = null;
    let dialogKeyHandler = null;
    let dialogBackdropHandler = null;

    function getDialogElements() {
        return {
            modal: document.getElementById('appDialogModal'),
            message: document.getElementById('appDialogMessage'),
            cancelBtn: document.getElementById('appDialogCancel'),
            confirmBtn: document.getElementById('appDialogConfirm'),
        };
    }

    function closeDialog(result) {
        const { modal, cancelBtn, confirmBtn } = getDialogElements();
        if (!modal) return;

        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');

        if (dialogKeyHandler) {
            document.removeEventListener('keydown', dialogKeyHandler);
            dialogKeyHandler = null;
        }

        if (dialogBackdropHandler && modal) {
            modal.removeEventListener('click', dialogBackdropHandler);
            dialogBackdropHandler = null;
        }

        cancelBtn?.removeEventListener('click', cancelBtn._dialogHandler);
        confirmBtn?.removeEventListener('click', confirmBtn._dialogHandler);

        const resolve = dialogResolve;
        dialogResolve = null;
        resolve?.(result);
    }

    U.dialog = (options = {}) => new Promise((resolve) => {
        const {
            message = '',
            confirmText = 'Confirmer',
            cancelText = 'Annuler',
            showCancel = true,
        } = options;

        const { modal, message: messageEl, cancelBtn, confirmBtn } = getDialogElements();
        if (!modal || !messageEl || !cancelBtn || !confirmBtn) {
            resolve(showCancel ? window.confirm(String(message)) : (window.alert(String(message)), true));
            return;
        }

        dialogResolve = resolve;
        messageEl.textContent = String(message);
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;
        cancelBtn.hidden = !showCancel;

        const onConfirm = () => closeDialog(true);
        const onCancel = () => closeDialog(false);

        confirmBtn._dialogHandler = onConfirm;
        cancelBtn._dialogHandler = onCancel;
        confirmBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);

        dialogKeyHandler = (event) => {
            if (event.key === 'Escape' && showCancel) closeDialog(false);
            if (event.key === 'Enter') closeDialog(true);
        };
        document.addEventListener('keydown', dialogKeyHandler);

        dialogBackdropHandler = (event) => {
            if (event.target === modal && showCancel) closeDialog(false);
        };
        modal.addEventListener('click', dialogBackdropHandler);

        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        (showCancel ? cancelBtn : confirmBtn).focus();
    });

    U.confirm = (message, options = {}) => U.dialog({
        message,
        confirmText: options.confirmText || 'Confirmer',
        cancelText: options.cancelText || 'Annuler',
        showCancel: true,
    });

    U.alert = (message, options = {}) => U.dialog({
        message,
        confirmText: options.okText || 'OK',
        showCancel: false,
    });
})();
