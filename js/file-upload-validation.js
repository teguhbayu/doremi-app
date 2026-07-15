(() => {
    const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
const errorIcon = `<img src="${window.DOREMI_APP_BASE_PATH}/images/gif/error.gif" alt="error.gif" class="tw:size-12 tw:rounded-full tw:object-cover tw:aspect-square"></img>`;

    function showInvalidFileAlert() {
        const message = 'Format file tidak didukung! Hanya file JPG, PNG, atau WEBP yang diperbolehkan.';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                iconHtml: errorIcon,
                title: 'Kesalahan',
                text: message,
            });
        } else {
            alert(message);
        }
    }

    document.addEventListener('change', (event) => {
        const input = event.target;
        if (!(input instanceof HTMLInputElement) || input.type !== 'file' || !input.files || !input.files.length) {
            return;
        }

        for (const file of input.files) {
            const extension = file.name.includes('.') ? file.name.split('.').pop().toLowerCase() : '';
            const mimeOk = ALLOWED_MIME_TYPES.includes(file.type);
            const extensionOk = ALLOWED_EXTENSIONS.includes(extension);

            if (!mimeOk && !extensionOk) {
                showInvalidFileAlert();
                input.value = '';
                return;
            }
        }
    });
})();
