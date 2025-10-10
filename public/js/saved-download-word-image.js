
$(document).ready(function () {

    /* -------------------------
       Helper: apply font recursively
       ------------------------- */
    function applyFontStyles(element, font, fontSizePt) {
        if (!element || element.nodeType !== 1) return;
        element.style.fontFamily = font;
        element.style.fontSize = `${fontSizePt}pt`;
        const children = element.children || [];
        for (let i = 0; i < children.length; i++) {
            applyFontStyles(children[i], font, fontSizePt);
        }
    }

    function applyFontToClone(element, font, fontSizePt) {
        // same as applyFontStyles but separate name (keeps parity with your original code)
        applyFontStyles(element, font, fontSizePt);
    }

    /* -------------------------
       Modal open (fadeIn) handlers
       ------------------------- */
    $(document).on('click', '#saved-download-pdf', function () {
        $('#saved-pdf-options-modal').fadeIn(200);
    });

    $(document).on('click', '#saved-download-image', function () {
        $('#saved-image-options-modal').fadeIn(200);
    });

    $(document).on('click', '#saved-download-word', function () {
        $('#saved-word-options-modal').fadeIn(200);
    });

    /* -------------------------
       Modal close (X or Cancel) handlers
       ------------------------- */
    $(document).on('click', '#closeSavedPdfModal, #saved-cancel-pdf-export', function () {
        $('#saved-pdf-options-modal').fadeOut(200);
    });

    $(document).on('click', '#closeSavedImageModal, #saved-cancel-image-export', function () {
        $('#saved-image-options-modal').fadeOut(200);
    });

    $(document).on('click', '#closeSavedWordModal, #saved-cancel-word-export', function () {
        $('#saved-word-options-modal').fadeOut(200);
    });

    /* -------------------------
       Click overlay to close
       (only if clicking the overlay itself)
       ------------------------- */
    $(document).on('click', '.custom-modal', function (e) {
        if (e.target === this) {
            $(this).fadeOut(200);
        }
    });

    /* =========================
       PDF generation (multi-page)
       Trigger: #saved-generate-pdf
       ========================= */
    $(document).on('click', '#saved-generate-pdf', async function (e) {
        e.preventDefault();

        const fontSize = document.getElementById('saved-pdf-font-size')?.value || "12";
        const font = document.getElementById('saved-pdf-font-style')?.value || "Arial";
        const paperSize = document.getElementById('saved-pdf-paper-size')?.value || "a4";

        const original = document.querySelector('.saved-area');
        if (!original) {
            alert('No content found to export (missing .saved-area).');
            return;
        }

        // clone and apply fonts (keeps original in DOM untouched)
        const clone = original.cloneNode(true);
        applyFontStyles(clone, font, fontSize);

        // hidden off-screen container so styles/layout compute
        const hiddenContainer = document.createElement('div');
        hiddenContainer.style.position = 'absolute';
        hiddenContainer.style.left = '-9999px';
        hiddenContainer.style.top = '0';
        hiddenContainer.appendChild(clone);
        document.body.appendChild(hiddenContainer);

        try {
            const canvas = await html2canvas(clone, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
            });

            // cleanup DOM
            document.body.removeChild(hiddenContainer);

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'pt', paperSize.toLowerCase());

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            const imgWidth = pageWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            // compute height of each slice (in canvas px) that maps to one pdf page
            const pageHeightInPixels = Math.floor((canvas.width * pageHeight) / imgWidth);

            let yOffset = 0;
            let isFirst = true;

            while (yOffset < canvas.height) {
                const sliceHeight = Math.min(pageHeightInPixels, canvas.height - yOffset);

                const pageCanvas = document.createElement('canvas');
                pageCanvas.width = canvas.width;
                pageCanvas.height = sliceHeight;

                const ctx = pageCanvas.getContext('2d');
                ctx.drawImage(canvas, 0, yOffset, canvas.width, sliceHeight, 0, 0, canvas.width, sliceHeight);

                const pageImgData = pageCanvas.toDataURL('image/png');

                if (!isFirst) pdf.addPage();
                // draw image to full page (0,0,width,height)
                pdf.addImage(pageImgData, 'PNG', 0, 0, pageWidth, pageHeight);

                yOffset += sliceHeight;
                isFirst = false;
            }

            pdf.save('assessment.pdf');
        } catch (err) {
            console.error('PDF export error:', err);
            alert('Failed to generate PDF. See console for details.');
            // ensure hidden container removed if error occurred
            if (document.body.contains(hiddenContainer)) {
                document.body.removeChild(hiddenContainer);
            }
        } finally {
            $('#saved-pdf-options-modal').fadeOut(200);
        }
    });


    /* =========================
       Image generation (PNG)
       Trigger: #saved-generate-image
       ========================= */
    $(document).on('click', '#saved-generate-image', async function (e) {
        e.preventDefault();

        const fontSize = document.getElementById('saved-image-font-size')?.value || "12";
        const font = document.getElementById('saved-image-font-style')?.value || "Arial";

        const original = document.querySelector('.saved-area');
        if (!original) {
            alert('No content found to export (missing .saved-area).');
            return;
        }

        const clone = original.cloneNode(true);
        applyFontStyles(clone, font, fontSize);

        const hiddenContainer = document.createElement('div');
        hiddenContainer.style.position = 'absolute';
        hiddenContainer.style.left = '-9999px';
        hiddenContainer.style.top = '0';
        hiddenContainer.appendChild(clone);
        document.body.appendChild(hiddenContainer);

        try {
            const canvas = await html2canvas(clone, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff'
            });
            const imageData = canvas.toDataURL('image/png');
            const link = document.createElement('a');
            link.href = imageData;
            link.download = 'assessment.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } catch (err) {
            console.error('Image export error:', err);
            alert('Failed to generate image. See console for details.');
        } finally {
            if (document.body.contains(hiddenContainer)) document.body.removeChild(hiddenContainer);
            $('#saved-image-options-modal').fadeOut(200);
        }
    });


    /* =========================
       Word generation (.doc)
       Trigger: #saved-generate-word
       ========================= */
    $(document).on('click', '#saved-generate-word', function (e) {
        e.preventDefault();

        const fontSize = document.getElementById('saved-word-font-size')?.value || "12";
        const font = document.getElementById('saved-word-font-style')?.value || "Arial";

        const original = document.querySelector('.saved-area');
        if (!original) {
            alert('No content found to export (missing .saved-area).');
            return;
        }

        const clone = original.cloneNode(true);
        applyFontToClone(clone, font, fontSize);

        const htmlContent = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office'
                  xmlns:w='urn:schemas-microsoft-com:office:word'
                  xmlns='http://www.w3.org/TR/REC-html40'>
            <head><meta charset='utf-8'><title>Export Word</title></head>
            <body style="font-family:${font}; font-size:${fontSize}pt;">${clone.innerHTML}</body>
            </html>
        `;

        const blob = new Blob(['\ufeff', htmlContent], { type: 'application/msword' });

        // use FileSaver or fallback
        if (window.saveAs) {
            saveAs(blob, 'assessment.doc');
        } else {
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'assessment.doc';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        $('#saved-word-options-modal').fadeOut(200);
    });

});

