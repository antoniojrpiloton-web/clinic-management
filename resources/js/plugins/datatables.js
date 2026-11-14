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

        const items = [];

        if (value.view) {
            items.push(`<li><button type="button" class="dropdown-item" data-view-url="${escapeHtml(value.view)}"><i class="bi bi-eye text-primary me-2"></i> View</button></li>`);
        }

        if (value.edit) {
            items.push(`<li><a href="${escapeHtml(value.edit)}" class="dropdown-item"><i class="bi bi-pencil text-warning me-2"></i> Edit</a></li>`);
        }

        if (value.delete) {
            if (items.length) {
                items.push('<li><hr class="dropdown-divider"></li>');
            }
            items.push(`
                <li>
                    <form method="POST" action="${escapeHtml(value.delete)}" class="d-inline" data-confirm="${escapeHtml(value.label || 'Record')} will be permanently deleted.">
                        <input type="hidden" name="_token" value="${escapeHtml(window.App.csrfToken)}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash text-danger me-2"></i> Delete</button>
                    </form>
                </li>`);
        }

        return `<div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-gear-fill me-1"></i> Action
            </button>
            <ul class="dropdown-menu shadow-sm">
                ${items.join('')}
            </ul>
        </div>`;
    },
};

function getTableExportData(api) {
    const $ = window.jQuery;
    const table = api.table().node();
    const headers = [];
    const validColIndexes = [];

    $(table).find('thead tr th').each(function (idx) {
        const th = $(this);
        const text = th.text().trim();
        const lower = text.toLowerCase();
        if (!th.hasClass('no-export') && lower !== 'action' && lower !== 'actions') {
            headers.push(text);
            validColIndexes.push(idx);
        }
    });

    const rows = [];
    $(table).find('tbody tr').each(function () {
        const tr = $(this);
        if (tr.hasClass('dataTables_empty') || tr.find('.spinner-border').length) {
            return;
        }
        const row = [];
        tr.find('td').each(function (colIdx) {
            if (validColIndexes.includes(colIdx)) {
                const cellText = $(this).text().trim().replace(/\s+/g, ' ');
                row.push(cellText);
            }
        });
        if (row.length) {
            rows.push(row);
        }
    });

    const pageTitle = (document.title || 'Table_Export').replace(/\s*—.*$/, '').trim();
    return { headers, rows, title: pageTitle };
}

function exportCsv(api) {
    const { headers, rows, title } = getTableExportData(api);
    if (!rows.length) {
        if (window.toastr) {
            window.toastr.info('No table data to export');
        }
        return;
    }
    const escapeCsv = (val) => `"${(val || '').replace(/"/g, '""')}"`;
    const csvContent = [
        headers.map(escapeCsv).join(','),
        ...rows.map((r) => r.map(escapeCsv).join(',')),
    ].join('\r\n');

    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${title.toLowerCase().replace(/[^a-z0-9]+/g, '_')}_${new Date().toISOString().slice(0, 10)}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
    if (window.toastr) {
        window.toastr.success('CSV export generated successfully');
    }
}

function exportExcel(api) {
    const { headers, rows, title } = getTableExportData(api);
    if (!rows.length) {
        if (window.toastr) {
            window.toastr.info('No table data to export');
        }
        return;
    }
    const escapeXml = (val) => (val || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    const xml = `<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">
 <Worksheet ss:Name="Records">
  <Table>
   <Row>${headers.map((h) => `<Cell><Data ss:Type="String">${escapeXml(h)}</Data></Cell>`).join('')}</Row>
   ${rows.map((r) => `<Row>${r.map((c) => `<Cell><Data ss:Type="String">${escapeXml(c)}</Data></Cell>`).join('')}</Row>`).join('\n   ')}
  </Table>
 </Worksheet>
</Workbook>`;

    const blob = new Blob([xml], { type: 'application/vnd.ms-excel' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `${title.toLowerCase().replace(/[^a-z0-9]+/g, '_')}_${new Date().toISOString().slice(0, 10)}.xls`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
    if (window.toastr) {
        window.toastr.success('Excel export generated successfully');
    }
}

function printTable(api) {
    const { headers, rows, title } = getTableExportData(api);
    if (!rows.length) {
        if (window.toastr) {
            window.toastr.info('No table data to print');
        }
        return;
    }
    const win = window.open('', '_blank');
    if (!win) {
        window.print();
        return;
    }
    const html = `<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>${title}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; padding: 24px; color: #232333; }
        h2 { font-size: 18px; margin-bottom: 4px; color: #696cff; font-weight: 700; }
        .meta { font-size: 11px; color: #6c757d; margin-bottom: 16px; border-bottom: 1px solid #dee2e6; padding-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #dee2e6; padding: 6px 10px; text-align: left; }
        th { background: #f8f9fa; font-weight: 600; text-transform: uppercase; font-size: 10px; color: #566a7f; }
        tr:nth-child(even) { background: #fafbfc; }
        @media print {
            body { padding: 0; }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body>
    <h2>${title}</h2>
    <div class="meta">Printed on ${new Date().toLocaleString()} &bull; Total Rows: ${rows.length}</div>
    <table>
        <thead><tr>${headers.map((h) => `<th>${h}</th>`).join('')}</tr></thead>
        <tbody>${rows.map((r) => `<tr>${r.map((c) => `<td>${c}</td>`).join('')}</tr>`).join('')}</tbody>
    </table>
</body>
</html>`;
    win.document.open();
    win.document.write(html);
    win.document.close();
    win.focus();
    setTimeout(() => {
        win.print();
        win.close();
    }, 300);
}

function copyTable(api) {
    const { headers, rows } = getTableExportData(api);
    if (!rows.length) {
        if (window.toastr) {
            window.toastr.info('No table data to copy');
        }
        return;
    }
    const text = [
        headers.join('\t'),
        ...rows.map((r) => r.join('\t')),
    ].join('\n');

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            if (window.toastr) {
                window.toastr.success('Table records copied to clipboard');
            }
        });
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        if (window.toastr) {
            window.toastr.success('Table records copied to clipboard');
        }
    }
}

function attachExportDropdown(target) {
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
        return;
    }
    const $ = window.jQuery;

    let api;
    try {
        api = $(target).DataTable();
    } catch (e) {
        return;
    }

    if (!api || !api.table) {
        return;
    }

    const wrapper = $(api.table().container());
    if (!wrapper.length || wrapper.find('.sneat-dt-export-group').length) {
        return;
    }

    const exportDropdown = $(`
        <div class="sneat-dt-export-group btn-group ms-2">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1.5 py-1.5 px-3 fw-medium shadow-2xs" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-download text-primary me-1"></i>
                <span>Export</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg py-2 border" style="min-width: 185px; z-index: 1060;">
                <li class="dropdown-header small text-uppercase fw-bold text-muted px-3 py-1">Export Table Data</li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-print"><i class="bi bi-printer text-primary fs-6"></i><span>Print Table</span></button></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-excel"><i class="bi bi-file-earmark-excel text-success fs-6"></i><span>Export Excel (.xls)</span></button></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-csv"><i class="bi bi-filetype-csv text-info fs-6"></i><span>Export CSV</span></button></li>
                <li><hr class="dropdown-divider my-1"></li>
                <li><button type="button" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3 dt-trigger-copy"><i class="bi bi-clipboard text-secondary fs-6"></i><span>Copy to Clipboard</span></button></li>
            </ul>
        </div>
    `);

    exportDropdown.find('.dt-trigger-print').on('click', (evt) => {
        evt.preventDefault();
        printTable(api);
    });
    exportDropdown.find('.dt-trigger-excel').on('click', (evt) => {
        evt.preventDefault();
        exportExcel(api);
    });
    exportDropdown.find('.dt-trigger-csv').on('click', (evt) => {
        evt.preventDefault();
        exportCsv(api);
    });
    exportDropdown.find('.dt-trigger-copy').on('click', (evt) => {
        evt.preventDefault();
        copyTable(api);
    });

    const filterContainer = wrapper.find('.dataTables_filter');
    if (filterContainer.length) {
        filterContainer.addClass('d-flex align-items-center justify-content-end');
        exportDropdown.appendTo(filterContainer);
    } else {
        const topRow = wrapper.find('.row:first-child');
        if (topRow.length) {
            const rightCol = topRow.find('> div:last-child');
            if (rightCol.length) {
                rightCol.addClass('d-flex align-items-center justify-content-end');
                exportDropdown.appendTo(rightCol);
            } else {
                topRow.append($('<div class="col-12 d-flex justify-content-end p-2"></div>').append(exportDropdown));
            }
        } else {
            const toolbar = $('<div class="d-flex justify-content-end p-2 border-bottom dt-export-bar"></div>').append(exportDropdown);
            wrapper.prepend(toolbar);
        }
    }
}

function scanAndAttachAll() {
    if (!window.jQuery || !window.jQuery.fn || !window.jQuery.fn.dataTable) {
        return;
    }
    const $ = window.jQuery;
    $('table.dataTable, table[id$="-table"]').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
            attachExportDropdown(this);
        }
    });
}

if (window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable) {
    const $ = window.jQuery;
    const defaultPageLength = Number(document.querySelector('meta[name="app-default-page-length"]')?.content || 25);

    $.extend(true, $.fn.dataTable.defaults, {
        pageLength: defaultPageLength,
        lengthMenu: [10, 25, 50, 100],
        autoWidth: false,
    });

    $(document).on('init.dt', function (e, settings) {
        attachExportDropdown(settings.nTable);
    });

    $(document).on('draw.dt', function (e, settings) {
        attachExportDropdown(settings.nTable);
    });
}

scanAndAttachAll();
if (window.jQuery) {
    window.jQuery(document).ready(scanAndAttachAll);
}
document.addEventListener('DOMContentLoaded', scanAndAttachAll);
window.addEventListener('load', scanAndAttachAll);
setTimeout(scanAndAttachAll, 250);
setTimeout(scanAndAttachAll, 800);
setTimeout(scanAndAttachAll, 2000);

window.attachDataTablesExport = attachExportDropdown;
if (window.App) {
    window.App.attachDataTablesExport = attachExportDropdown;
}

function initTable(table) {
    const defaultPageLength = Number(document.querySelector('meta[name="app-default-page-length"]')?.content || 25);
    const url = table.dataset.url;
    const filterForm = table.dataset.filters ? document.getElementById(table.dataset.filters) : null;
    const pageLength = Number(table.dataset.pageLength || defaultPageLength);

    const options = {
        pageLength,
        lengthMenu: [10, 25, 50, 100],
        autoWidth: false,
        language: {
            search: '',
            searchPlaceholder: 'Search...',
            emptyTable: 'No records found',
            processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading…',
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
    attachExportDropdown(table);

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
