document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('service_id');
    
    if (select) {
        select.addEventListener('mouseenter', () => {
            select.style.borderColor = '#a0a0a0';
        });
        
        select.addEventListener('mouseleave', () => {
            if (!document.activeElement === select) {
                select.style.borderColor = '#e0e0e0';
            }
        });
        
        select.addEventListener('focus', () => {
            select.style.borderColor = '#611e71';
        });
        
        select.addEventListener('blur', () => {
            select.style.borderColor = '#e0e0e0';
        });
    }
});