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

        // Get header elements
        const original = document.querySelector('.generated-area');
        const headerText = original.querySelector('.header-text')?.innerHTML || '';
        
        // Get content HTML and remove ALL images
        let contentHTML = original.innerHTML;
        
        // Remove the entire assessment-header div
        contentHTML = contentHTML.replace(/<div class="assessment-header">[\s\S]*?<\/div>/, '');
        
        // Remove any remaining img tags
        contentHTML = contentHTML.replace(/<img[^>]*>/g, '');
        
        const html =
        `
        <html xmlns:w="urn:schemas-microsoft-com:office:word">
        <head>
            <meta charset="utf-8">
            <style>
                body {
                    font-family: ${font};
                    font-size: ${fontSize}pt;
                    line-height: 1.25;
                    margin: 0;
                    padding: 20px;
                }
                .header-table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                .header-table td {
                    vertical-align: middle;
                    text-align: center;
                }
                .header-text {
                    text-align: center;
                    padding: 0 20px;
                }
                .header-text p {
                    margin: 5px 0;
                    line-height: 1.3;
                }
            </style>
        </head>
        <body>
            <table class="header-table">
                <tr>
                    <td>
                        <div class="header-text">${headerText}</div>
                    </td>
                </tr>
            </table>
            ${contentHTML}
        </body>
        </html>
        `;

        const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
        saveAs(blob, 'assessment.doc');
        document.getElementById('word-options-modal').style.display = 'none';
    }
});

// Rest of your functions remain the same...
function convertImagesToBase64(container) {
    const images = container.querySelectorAll("img");
    const promises = [];

    images.forEach(img => {
        const src = img.src;
        const promise = fetch(src)
            .then(r => r.blob())
            .then(blob =>
                new Promise(resolve => {
                    const reader = new FileReader();
                    reader.onloadend = () => {
                        img.src = reader.result; 
                        resolve();
                    };
                    reader.readAsDataURL(blob);
                })
            );
        promises.push(promise);
    });

    return Promise.all(promises);
}

function inlineAllComputedStyles(element) {
    const tree = document.createTreeWalker(element, NodeFilter.SHOW_ELEMENT, null);

    while (tree.nextNode()) {
        const node = tree.currentNode;
        const computed = window.getComputedStyle(node);

        for (let prop of computed) {
            node.style[prop] = computed.getPropertyValue(prop);
        }
    }
}

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

        const pdf = new jsPDF("p", "pt", paperSize);

        const imgWidth = pdf.internal.pageSize.getWidth();
        const pageHeight = pdf.internal.pageSize.getHeight();

        // Correct proportional slice height
        const sliceHeight = pageHeight * (canvas.width / imgWidth);

        let y = 0;

        while (y < canvas.height) {
            const pageCanvas = document.createElement("canvas");
            const ctx = pageCanvas.getContext("2d");

            // Remaining part to slice
            const currentSliceHeight = Math.min(sliceHeight, canvas.height - y);

            pageCanvas.width = canvas.width;
            pageCanvas.height = currentSliceHeight;

            ctx.drawImage(
                canvas,
                0,
                y,
                canvas.width,
                currentSliceHeight,
                0,
                0,
                canvas.width,
                currentSliceHeight
            );

            const imgData = pageCanvas.toDataURL("image/png");

            // Keep aspect ratio — DO NOT stretch to full page height
            const scaledHeight = currentSliceHeight * (imgWidth / canvas.width);

            if (y > 0) pdf.addPage();
            pdf.addImage(imgData, "PNG", 0, 0, imgWidth, scaledHeight);

            y += sliceHeight;
        }

        pdf.save("assessment.pdf");
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
