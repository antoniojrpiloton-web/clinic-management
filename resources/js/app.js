const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

function notify(type, message, title = '') {
    if (window.toastr) {
        window.toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: 4000,
        };
        window.toastr[type === 'danger' ? 'error' : type](message, title);
        return;
    }

    let container = document.getElementById('app-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'app-toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }

    const color = { error: 'danger', success: 'success', warning: 'warning', info: 'info', danger: 'danger' }[type] ?? 'secondary';
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${color} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = '<div class="d-flex"><div class="toast-body"></div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button></div>';
    toast.querySelector('.toast-body').textContent = message;
    container.appendChild(toast);

    toast.addEventListener('hidden.bs.toast', () => toast.remove());
    window.bootstrap.Toast.getOrCreateInstance(toast, { delay: 4000 }).show();
}

async function confirmAction({ title = 'Are you sure?', text = '', confirmText = 'Yes, continue', icon = 'warning' } = {}) {
    if (window.Swal) {
        const result = await window.Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#2563eb',
            reverseButtons: true,
            focusCancel: true,
        });

        return result.isConfirmed;
    }

    return window.confirm(text ? `${title}\n\n${text}` : title);
}

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json, text/html',
            ...(options.headers ?? {}),
        },
    });

    if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
    }

    return response;
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[char]);
}

window.App = { notify, confirmAction, request, escapeHtml, csrfToken };

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-toggle="sidebar"]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('.app-sidebar')?.classList.toggle('show');
            document.querySelector('.sidebar-backdrop')?.classList.toggle('show');
        });
    });

    const flash = document.getElementById('flash-messages');
    if (flash) {
        JSON.parse(flash.textContent).forEach(({ type, message }) => notify(type, message));
    }

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        window.bootstrap.Tooltip.getOrCreateInstance(element);
    });

    const searchInputs = document.querySelectorAll('.topbar-search-box input');
    document.addEventListener('keydown', (event) => {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchInputs[0]?.focus();
        }
    });
});

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) {
        return;
    }

    event.preventDefault();

    const confirmed = await confirmAction({
        title: form.dataset.confirmTitle ?? 'Are you sure?',
        text: form.dataset.confirm,
        confirmText: form.dataset.confirmButton ?? 'Yes, proceed',
    });

    if (confirmed) {
        form.dataset.confirmed = '1';
        form.requestSubmit();
    }
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
        return;
    }

    form.querySelectorAll('button[type="submit"]:not([data-no-disable])').forEach((button) => {
        button.disabled = true;
        if (button.dataset.loadingText) {
            button.innerHTML = `<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>${escapeHtml(button.dataset.loadingText)}`;
        }
    });
});
