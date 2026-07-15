// Captures the report page's rendered Chart.js canvases and submits them with
// the "Export PDF" request so they can be embedded in the generated PDF.
//
// Each report page must declare its charts before loading this script, e.g.:
//   window.reportChartSpecs = [{ id: 'chartStatus', title: 'Distribusi Status' }, ...];
(function () {
    const exportBtn = document.getElementById('exportPdfBtn');
    if (!exportBtn) return;

    const specs = Array.isArray(window.reportChartSpecs) ? window.reportChartSpecs : [];

    function captureCharts() {
        const captured = [];
        specs.forEach(spec => {
            const canvas = document.getElementById(spec.id);
            if (!canvas || !canvas.width || !canvas.height) return;

            // Composite onto a white background (chart canvases are transparent)
            const flat = document.createElement('canvas');
            flat.width = canvas.width;
            flat.height = canvas.height;
            const ctx = flat.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, flat.width, flat.height);
            ctx.drawImage(canvas, 0, 0);

            captured.push({ title: spec.title, image: flat.toDataURL('image/png') });
        });
        return captured;
    }

    exportBtn.addEventListener('click', function (e) {
        e.preventDefault();

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = exportBtn.getAttribute('href');

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'chart_images';
        input.value = JSON.stringify(captureCharts());
        form.appendChild(input);

        document.body.appendChild(form);
        form.submit();
        form.remove();
    });
})();
