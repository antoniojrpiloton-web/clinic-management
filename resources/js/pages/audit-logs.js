
document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('audit-log-modal');

    if (!modalElement) {
        return;
    }

    const modal = window.bootstrap.Modal.getOrCreateInstance(modalElement);
    const body = modalElement.querySelector('.modal-body');

    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-view-url]');

        if (!button) {
            return;
        }

        body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>';
        modal.show();

        try {
            const response = await window.App.request(button.dataset.viewUrl);
            body.innerHTML = await response.text();
        } catch (error) {
            body.innerHTML = '<div class="alert alert-danger mb-0">Unable to load the audit log entry.</div>';
        }
    });
});

