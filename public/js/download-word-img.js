function applyFontStyles(element, font, fontSize) {
    element.style.fontFamily = font;
    element.style.fontSize = `${fontSize}pt`;
    [...element.children].forEach(child => applyFontStyles(child, font, fontSize));
}

// ===================== IMAGE EXPORT =====================
document.addEventListener('click', async function (e) {
    if (e.target.closest('#download-image')) {
        const modal = document.getElementById('image-options-modal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('show'), 10);
    }

    if (e.target.closest('#generate-image')) {
        const font = document.getElementById('image-font-style').value;
        const fontSize = document.getElementById('image-font-size').value;
        const original = document.querySelector('.generated-area');
        const clone = original.cloneNode(true);
        applyFontStyles(clone, font, fontSize);

        const hidden = document.createElement('div');
        hidden.style.position = 'absolute';
        hidden.style.left = '-9999px';
        hidden.appendChild(clone);
        document.body.appendChild(hidden);

        const canvas = await html2canvas(clone, { scale: 2, backgroundColor: '#fff' });
        document.body.removeChild(hidden);

        const link = document.createElement('a');
        link.href = canvas.toDataURL('image/png');
        link.download = 'assessment.png';
        link.click();

        document.getElementById('image-options-modal').style.display = 'none';
    }
});

// ===================== WORD EXPORT =====================
document.addEventListener('click', function (e) {
    if (e.target.closest('#download-word')) {
        const modal = document.getElementById('word-options-modal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('show'), 10);
    }

    if (e.target.closest('#generate-word')) {
        const font = document.getElementById('word-font-style').value;
        const fontSize = document.getElementById('word-font-size').value;
        const original = document.querySelector('.generated-area');
        const clone = original.cloneNode(true);
        applyFontStyles(clone, font, fontSize);

        const html = `
            <html>
            <head><meta charset="utf-8"></head>
            <body style="font-family:${font}; font-size:${fontSize}pt;">${clone.innerHTML}</body>
            </html>
        `;

        const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
        saveAs(blob, 'assessment.doc');
        document.getElementById('word-options-modal').style.display = 'none';
    }
});

// ===================== PDF EXPORT =====================
document.addEventListener('click', async function (e) {
    if (e.target.closest('#download-pdf')) {
        const modal = document.getElementById('pdf-options-modal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('show'), 10);
    }

    if (e.target.closest('#generate-pdf')) {
        const font = document.getElementById('pdf-font-style').value;
        const fontSize = document.getElementById('pdf-font-size').value;
        const paperSize = document.getElementById('pdf-paper-size').value;
        const { jsPDF } = window.jspdf;

        const original = document.querySelector('.generated-area');
        const clone = original.cloneNode(true);
        applyFontStyles(clone, font, fontSize);

        const hidden = document.createElement('div');
        hidden.style.position = 'absolute';
        hidden.style.left = '-9999px';
        hidden.appendChild(clone);
        document.body.appendChild(hidden);

        const canvas = await html2canvas(clone, { scale: 2, backgroundColor: '#fff' });
        document.body.removeChild(hidden);

        const pdf = new jsPDF('p', 'pt', paperSize);
        const imgWidth = pdf.internal.pageSize.getWidth();
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        const pageHeight = pdf.internal.pageSize.getHeight();
        let y = 0;

        while (y < canvas.height) {
            const pageCanvas = document.createElement('canvas');
            const ctx = pageCanvas.getContext('2d');
            const sliceHeight = Math.min(canvas.height - y, canvas.width * (pageHeight / imgWidth));
            pageCanvas.width = canvas.width;
            pageCanvas.height = sliceHeight;

            ctx.drawImage(canvas, 0, y, canvas.width, sliceHeight, 0, 0, canvas.width, sliceHeight);
            const imgData = pageCanvas.toDataURL('image/png');

            if (y > 0) pdf.addPage();
            pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, pageHeight);

            y += sliceHeight;
        }

        pdf.save('assessment.pdf');
        document.getElementById('pdf-options-modal').style.display = 'none';
    }
});

    $(document).on('click', '#closePdfModal, #saved-cancel-pdf-export', function () {
        $('#pdf-options-modal').fadeOut(200);
    });

    $(document).on('click', '#closeImageModal, #saved-cancel-image-export', function () {
        $('#image-options-modal').fadeOut(200);
    });

    $(document).on('click', '#closeWordModal, #saved-cancel-word-export', function () {
        $('#word-options-modal').fadeOut(200);
    });
