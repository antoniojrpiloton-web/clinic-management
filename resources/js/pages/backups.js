
document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('[data-has-pending="1"]');

    if (container) {
        setTimeout(() => window.location.reload(), 10000);
    }
});

