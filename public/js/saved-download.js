document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'saved-cancel-word-export') {
        const modal = document.getElementById('saved-word-options-modal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); 
    }
});

document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'saved-cancel-pdf-export') {
        const modal = document.getElementById('saved-pdf-options-modal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); 
    }
});

document.addEventListener('click', function (e) {
    if (e.target && e.target.id === 'saved-cancel-image-export') {
        const modal = document.getElementById('saved-image-options-modal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300); 
    }
});

function applyFontStyles(element, font, fontSize) {
    element.style.fontFamily = font;
    element.style.fontSize = `${fontSize}pt`;
    [...element.children].forEach(child => applyFontStyles(child, font, fontSize));
}

document.addEventListener('click', function (e) {
    if (e.target.closest('#saved-download-pdf')) {
        const modal = document.getElementById('saved-pdf-options-modal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('show'), 10);
    }
});


document.addEventListener('click', async function (e) {
    if (!e.target.closest('#saved-generate-pdf')) return;

    const fontSize = document.getElementById('saved-pdf-font-size').value || "12";
    const font = document.getElementById('saved-pdf-font-style').value || "Arial";
    const paperSize = document.getElementById('saved-pdf-paper-size').value || "a4";

    const { jsPDF } = window.jspdf;

    const original = document.querySelector('.saved-area');
    const clone = original.cloneNode(true);
    applyFontStyles(clone, font, fontSize);

    // Preserve layout/styles by adding it to a hidden off-screen area
    const hiddenContainer = document.createElement('div');
    hiddenContainer.style.position = 'absolute';
    hiddenContainer.style.left = '-9999px';
    hiddenContainer.style.top = '0';
    hiddenContainer.appendChild(clone);
    document.body.appendChild(hiddenContainer);

    const canvas = await html2canvas(clone, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff',
    });

    document.body.removeChild(hiddenContainer);

    const imgData = canvas.toDataURL('image/png');
    const pdf = new jsPDF('p', 'pt', paperSize.toLowerCase());

    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();

    const inch = 72; // 2.54 cm
    const bottomMargin = inch;
    const topMargin = inch;

    const imgWidth = pageWidth;
    const imgHeight = (canvas.height * imgWidth) / canvas.width;

    let position = 0;
    let remainingHeight = imgHeight;
    let isFirstPage = true;

    let yOffset = 0;
    isFirstPage = true;

    while (yOffset < canvas.height) {
        const currentUsableHeight = pageHeight - bottomMargin - (isFirstPage ? 0 : topMargin);
        const pageHeightInPixels = Math.floor((canvas.width * currentUsableHeight) / imgWidth);

        const pageCanvas = document.createElement('canvas');
        pageCanvas.width = canvas.width;
        pageCanvas.height = pageHeightInPixels;

        const context = pageCanvas.getContext('2d');
        context.drawImage(
            canvas,
            0,
            yOffset,
            canvas.width,
            pageHeightInPixels,
            0,
            0,
            canvas.width,
            pageHeightInPixels
        );

        const pageImgData = pageCanvas.toDataURL('image/png');
        if (!isFirstPage) pdf.addPage();
        const addYOffset = isFirstPage ? 0 : topMargin;

        pdf.addImage(pageImgData, 'PNG', 0, addYOffset, imgWidth, currentUsableHeight);

        yOffset += pageHeightInPixels;
        isFirstPage = false;
    }

    pdf.save('assessment.pdf');
    document.getElementById('saved-pdf-options-modal').style.display = 'none';
});
