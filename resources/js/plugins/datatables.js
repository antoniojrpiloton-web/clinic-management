/**
 * Generic DataTables initialiser (DataTables 3 vanilla API, no jQuery).
 *
 * Markup is produced by the <x-datatable> Blade component:
 *  - data-url        → server-side processing endpoint (omit for client-side tables)
 *  - data-columns    → JSON column definitions [{data, title, renderType, orderable, className}]
 *  - data-filters    → id of a <form> whose fields are sent with each request
 */
const { escapeHtml } = window.App;

const renderers = {
    text: (value) => escapeHtml(value ?? '—'),

    badge: (value) => (value ? `<span class="badge badge-pill badge-${escapeHtml(value.color)}">${escapeHtml(value.text)}</span>` : ''),

    badges: (values) => (values ?? [])
        .map((value) => `<span class="badge badge-pill badge-gray me-1">${escapeHtml(value)}</span>`)
        .join('') || '<span class="text-muted">—</span>',

    user: (value) => `
        <div class="d-flex align-items-center gap-2">
            <span class="avatar">${escapeHtml(value.initials)}</span>
            <div class="lh-sm">
                <div class="fw-semibold text-dark">${escapeHtml(value.name)}</div>
                <small class="text-muted">${escapeHtml(value.email)}</small>
            </div>
        </div>`,

    actions: (value) => {
        if (!value) {
            return '';
        }

        const buttons = [];

        if (value.view) {
            buttons.push(`<button type="button" class="btn btn-sm btn-outline-forest" data-view-url="${escapeHtml(value.view)}" title="View"><i class="bi bi-eye"></i> View</button>`);
        }

        if (value.edit) {
            buttons.push(`<a href="${escapeHtml(value.edit)}" class="btn btn-sm btn-outline-forest" title="Edit"><i class="bi bi-pencil-square"></i></a>`);
        }

        if (value.delete) {
            buttons.push(`
                <form method="POST" action="${escapeHtml(value.delete)}" class="d-inline" data-confirm="${escapeHtml(value.label)} will be permanently deleted.">
                    <input type="hidden" name="_token" value="${escapeHtml(window.App.csrfToken)}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="bi bi-trash"></i></button>
                </form>`);
        }

        return `<div class="d-inline-flex gap-1">${buttons.join('')}</div>`;
    },
};

function initTable(table) {
    const url = table.dataset.url;
    const filterForm = table.dataset.filters ? document.getElementById(table.dataset.filters) : null;
    const pageLength = Number(table.dataset.pageLength || 25);

    const options = {
        pageLength,
        lengthMenu: [10, 25, 50, 100],
        autoWidth: false,
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            emptyTable: 'No records found',
            processing: '<div class="spinner-border spinner-border-sm text-success" role="status"></div> Loading…',
        },
    };

    if (url) {
        const columns = JSON.parse(table.dataset.columns || '[]');

        Object.assign(options, {
            serverSide: true,
            processing: true,
            searchDelay: 400,
            order: [],
            ajax: {
                url,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: (payload) => {
                    if (filterForm) {
                        new FormData(filterForm).forEach((value, key) => {
                            payload[key] = value;
                        });
                    }

                    return payload;
                },
            },
            columns: columns.map((column) => ({
                data: column.data,
                title: column.title,
                orderable: column.orderable ?? false,
                searchable: false,
                className: column.className ?? '',
                render: (data, type, row) => {
                    const rType = column.renderType ?? column.type ?? 'text';
                    const fn = renderers[rType] ?? renderers.text;
                    return fn(data, row);
                },
            })),
        });
    } else {
        options.order = [];
        options.columnDefs = [{ targets: 'no-sort', orderable: false }];
    }

    const dataTable = new window.DataTable(table, options);

    if (filterForm) {
        const reload = () => dataTable.ajax.reload();
        filterForm.addEventListener('change', reload);
        filterForm.addEventListener('submit', (event) => {
            event.preventDefault();
            reload();
        });
        filterForm.addEventListener('reset', () => setTimeout(reload));
    }

    return dataTable;
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('table[data-datatable]').forEach(initTable);
});
