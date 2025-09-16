function applyFontStyles(element, font, fontSize) {
    element.style.fontFamily = font;
    element.style.fontSize = `${fontSize}pt`;
    [...element.children].forEach(child => applyFontStyles(child, font, fontSize));
}

document.addEventListener('click', function (e) {
    if (e.target.closest('#saved-download-image')) {
        const modal = document.getElementById('saved-image-options-modal');
        modal.style.display = 'block';
        // Let the browser render before applying transition
        setTimeout(() => modal.classList.add('show'), 10);
    }
});


// Handle image generation
document.addEventListener('click', async function (e) {
    if (!e.target.closest('#saved-generate-image')) return;

    const fontSize = document.getElementById('saved-image-font-size').value || "12";
    const font = document.getElementById('saved-image-font-style').value || "Arial";

    const original = document.querySelector('.saved-area');
    const clone = original.cloneNode(true);
    applyFontStyles(clone, font, fontSize);

    // Render the clone in a hidden container
    const hiddenContainer = document.createElement('div');
    hiddenContainer.style.position = 'absolute';
    hiddenContainer.style.left = '-9999px';
    hiddenContainer.style.top = '0';
    hiddenContainer.appendChild(clone);
    document.body.appendChild(hiddenContainer);

    const canvas = await html2canvas(clone, {
        scale: 2,
        useCORS: true,
        backgroundColor: '#ffffff'
    });

    document.body.removeChild(hiddenContainer);

    // Create download link
    const imageData = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.href = imageData;
    link.download = 'assessment.png';
    link.click();

    // Hide modal
    document.getElementById('saved-image-options-modal').style.display = 'none';
});


function applyFontToClone(element, font, fontSize) {
    element.style.fontFamily = font;
    element.style.fontSize = `${fontSize}pt`;
    [...element.children].forEach(child => applyFontToClone(child, font, fontSize));
}

document.addEventListener('click', function (e) {
    if (e.target.closest('#saved-download-word')) {
        const modal = document.getElementById('saved-word-options-modal');
        modal.style.display = 'block';
        setTimeout(() => modal.classList.add('show'), 10);
    }
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('#saved-generate-word')) return;

    const fontSize = document.getElementById('saved-word-font-size').value || "12";
    const font = document.getElementById('saved-word-font-style').value || "Arial";

    const original = document.querySelector('.saved-area');
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

    const blob = new Blob(['\ufeff', htmlContent], {
        type: 'application/msword'
    });

    saveAs(blob, 'assessment.doc');
    document.getElementById('saved-word-options-modal').style.display = 'none';
});
