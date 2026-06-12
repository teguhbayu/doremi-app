<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Access denied');
}
?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const url = new URL(window.location.href);
        const suksesIcon = `<img src="/doremi-app/images/gif/success.gif" alt="sukses.gif" class="tw:size-12 tw:rounded-full tw:object-cover tw:aspect-square"></img>`
        const errorIcon = `<img src="/doremi-app/images/gif/error.gif" alt="sukses.gif" class="tw:size-12 tw:rounded-full tw:object-cover tw:aspect-square"></img>`
        switch (urlParams.get("status")) {
            case "success":
                Swal.fire({
                    icon: "success",
                    iconHtml: suksesIcon,
                    title: "Sukses",
                    text: urlParams.get("message") ?? "",
                });
                setTimeout(() => {
                    url.search = '';
                    window.history.replaceState(null, '', url.toString());
                }, 3000)
                break;
            case "error":
                Swal.fire({
                    icon: "error",
                    title: "Kesalahan",
                    iconHtml: errorIcon,
                    text: urlParams.get("message") ?? "",
                });
                setTimeout(() => {
                    url.search = '';
                    window.history.replaceState(null, '', url.toString());
                }, 3000)
                break;

            default:
                break;
        }

    })
</script>