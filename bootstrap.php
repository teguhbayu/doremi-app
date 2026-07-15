<?php
if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Access denied');
}
require_once __DIR__ . '/utils/url.php';
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="<?= app_url('js/animations.js') ?>?v=<?= filemtime(__DIR__ . '/js/animations.js') ?>"></script>
<script src="<?= app_url('js/file-upload-validation.js') ?>?v=<?= filemtime(__DIR__ . '/js/file-upload-validation.js') ?>"></script>
<script>
    (() => {
        const tables = document.querySelectorAll('table.doremi-table');

        tables.forEach(table => {
            if (table.dataset.numbered === 'true') {
                return;
            }

            const headerRow = table.querySelector('thead tr');
            if (!headerRow) {
                return;
            }

            const firstHeaderText = headerRow.firstElementChild
                ? headerRow.firstElementChild.textContent.trim().toLowerCase()
                : '';
            const hasNumberHeader = firstHeaderText === 'no' || headerRow.querySelector('.row-number-col');

            if (!hasNumberHeader) {
                const numberHeader = document.createElement('th');
                numberHeader.scope = 'col';
                numberHeader.className = 'text-center align-middle row-number-col';
                numberHeader.textContent = 'No';
                numberHeader.setAttribute('data-dt-order', 'disable');
                headerRow.prepend(numberHeader);

                table.querySelectorAll('tbody tr').forEach(row => {
                    const numberCell = document.createElement('td');
                    numberCell.className = 'text-center align-middle row-number-cell';
                    numberCell.setAttribute('aria-label', 'Nomor');
                    row.prepend(numberCell);
                });
            }

            table.dataset.numbered = 'true';
        });
    })();
</script>
