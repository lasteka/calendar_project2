// js/accardion.js
document.addEventListener('DOMContentLoaded', () => {
    const headers = document.querySelectorAll('.accordion-header');
    
    if (!headers.length) {
        console.error('Nav atrastas akordeona galvenes!');
        return;
    }

    headers.forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const isActive = header.classList.contains('active');

            // Aizver visas pārējās sadaļas
            document.querySelectorAll('.accordion-header, .accordion-content').forEach(el => {
                el.classList.remove('active');
            });

            // Atver/aizver klikšķināto
            if (!isActive) {
                header.classList.add('active');
                content.classList.add('active');
            }
        });
    });
});