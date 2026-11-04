/**
 * Clinic Management - core application script (vanilla ES6).
 *
 * Loaded on every authenticated and guest page. Plugin-specific code lives in
 * resources/js/plugins and page-specific code in resources/js/pages; those
 * files are only loaded by the views that need them.
 */

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/**
 * Show a notification using Toastr when available, falling back to a
 * Bootstrap toast so the core bundle does not depend on jQuery.
 */
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

/**
 * Ask for confirmation using SweetAlert2 when loaded, otherwise window.confirm.
 */
async function confirmAction({ title = 'Are you sure?', text = '', confirmText = 'Yes, continue', icon = 'warning' } = {}) {
    if (window.Swal) {
        const result = await window.Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText: confirmText,
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            reverseButtons: true,
            focusCancel: true,
        });

        return result.isConfirmed;
    }

    return window.confirm(text ? `${title}\n\n${text}` : title);
}

/**
 * fetch() wrapper that sends the CSRF token and expects JSON or HTML.
 */
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

/**
 * Escape a value for safe insertion into HTML strings.
 */
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

/* ---------------------------------------------------------------------
 | Global behaviours (event delegation, no plugin initialisation)
 * ------------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', () => {
    // Sidebar toggle for small screens.
    document.querySelectorAll('[data-toggle="sidebar"]').forEach((button) => {
        button.addEventListener('click', () => document.body.classList.toggle('sidebar-open'));
    });

    // Server flash messages.
    const flash = document.getElementById('flash-messages');

    if (flash) {
        JSON.parse(flash.textContent).forEach(({ type, message }) => notify(type, message));
    }

    // Bootstrap tooltips (only when present on the page).
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        window.bootstrap.Tooltip.getOrCreateInstance(element);
    });

    // Topbar quick search (Ctrl+/ shortcut + live sidebar menu filter).
    const navSearch = document.getElementById('global-nav-search');

    if (navSearch) {
        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key === '/') {
                event.preventDefault();
                navSearch.focus();
            }
        });

        navSearch.addEventListener('input', () => {
            const term = navSearch.value.trim().toLowerCase();
            document.querySelectorAll('.app-sidebar .menu-item').forEach((item) => {
                const match = !term || item.textContent.toLowerCase().includes(term);
                item.classList.toggle('d-none', !match);
            });
        });
    }
});

// Confirm before submitting any form marked with data-confirm.
document.addEventListener('submit', async (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) {
        return;
    }

    event.preventDefault();

    const confirmed = await confirmAction({
        title: form.dataset.confirmTitle ?? 'Are you sure?',
        text: form.dataset.confirm,
        confirmText: form.dataset.confirmButton ?? 'Yes, delete it',
    });

    if (confirmed) {
        form.dataset.confirmed = '1';
        form.requestSubmit();
    }
});

// Prevent double submissions.
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
