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
            confirmButtonColor: '#1b5e3a',
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

    const searchWrapper = document.getElementById('global-search-wrapper');
    const searchInput = document.getElementById('global-search-input');
    const searchDropdown = document.getElementById('global-search-dropdown');
    const searchResults = document.getElementById('global-search-results');
    const searchSpinner = document.getElementById('global-search-spinner');
    const searchIcon = document.getElementById('global-search-icon');
    const searchClear = document.getElementById('global-search-clear');
    const searchCount = document.getElementById('global-search-count');
    const categoryContainer = document.getElementById('global-search-categories');

    if (searchWrapper && searchInput && searchDropdown && searchResults) {
        const searchUrl = searchWrapper.dataset.searchUrl || '/global-search';
        let activeCategory = 'all';
        let activeIndex = -1;
        let debounceTimer = null;
        let abortController = null;
        let hasLoadedInitial = false;

        const highlightMatch = (text, query) => {
            const safe = escapeHtml(text ?? '');
            const cleanQuery = (query ?? '').trim();
            if (!cleanQuery) {
                return safe;
            }
            const escapedPattern = cleanQuery.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return safe.replace(new RegExp(`(${escapedPattern})`, 'gi'), '<mark>$1</mark>');
        };

        const getSelectableItems = () => Array.from(searchResults.querySelectorAll('.gs-result-item'));

        const updateActiveItem = (newIndex) => {
            const items = getSelectableItems();
            items.forEach((el) => el.classList.remove('is-active'));
            if (items.length === 0) {
                activeIndex = -1;
                return;
            }
            activeIndex = ((newIndex % items.length) + items.length) % items.length;
            const current = items[activeIndex];
            current.classList.add('is-active');
            current.scrollIntoView({ block: 'nearest' });
        };

        const openDropdown = () => {
            searchDropdown.classList.remove('d-none');
            if (!hasLoadedInitial) {
                hasLoadedInitial = true;
                fetchResults();
            }
        };

        const closeDropdown = () => {
            searchDropdown.classList.add('d-none');
            activeIndex = -1;
        };

        const renderResults = (data) => {
            const query = data.query || '';
            const groups = data.groups || [];
            activeIndex = -1;

            if (searchCount) {
                searchCount.textContent = query
                    ? `${data.total ?? 0} result${data.total === 1 ? '' : 's'} found`
                    : 'Quick Navigation & Recent';
            }

            if (groups.length === 0) {
                searchResults.innerHTML = `
                    <div class="text-center text-muted py-4 px-3">
                        <i class="bi bi-search fs-4 d-block mb-2 opacity-50"></i>
                        <div class="fw-semibold text-dark small mb-1">No matching records for "${escapeHtml(query)}"</div>
                        <div class="small">Try searching by Patient Name, Student/Employee ID, Visit #, PE #, Medicine Name, or Report title.</div>
                    </div>
                `;
                return;
            }

            let html = '';
            groups.forEach((group) => {
                html += `
                    <div class="gs-group-header">
                        <i class="bi ${escapeHtml(group.icon || 'bi-folder')}"></i>
                        <span>${escapeHtml(group.label)}</span>
                    </div>
                `;

                (group.items || []).forEach((item) => {
                    const actionsHtml = (item.actions || []).map((act) => `
                        <a href="${escapeHtml(act.url)}" class="gs-action-chip" data-gs-action="1">
                            <i class="bi ${escapeHtml(act.icon || 'bi-arrow-right')}"></i>
                            <span>${escapeHtml(act.label)}</span>
                        </a>
                    `).join('');

                    html += `
                        <div class="gs-result-item" data-url="${escapeHtml(item.url)}" role="option" tabindex="-1">
                            <div class="d-flex align-items-center gap-2 min-w-0 flex-grow-1">
                                <div class="gs-result-icon">
                                    <i class="bi ${escapeHtml(item.icon || 'bi-arrow-right-circle')}"></i>
                                </div>
                                <div class="gs-result-main">
                                    <div class="gs-result-title">${highlightMatch(item.title, query)}</div>
                                    <div class="gs-result-subtitle">${highlightMatch(item.subtitle, query)}</div>
                                </div>
                            </div>
                            <div class="gs-result-right">
                                ${actionsHtml}
                                ${item.badge ? `<span class="badge-pill ${escapeHtml(item.badge_class || 'badge-soft-gray')}">${escapeHtml(item.badge)}</span>` : ''}
                            </div>
                        </div>
                    `;
                });
            });

            searchResults.innerHTML = html;
            const firstItems = getSelectableItems();
            if (firstItems.length > 0 && query) {
                updateActiveItem(0);
            }
        };

        const fetchResults = async () => {
            const q = searchInput.value.trim();

            if (abortController) {
                abortController.abort();
            }
            abortController = new AbortController();

            searchSpinner?.classList.remove('d-none');
            searchIcon?.classList.add('d-none');

            try {
                const params = new URLSearchParams({
                    q,
                    category: activeCategory,
                });
                const res = await fetch(`${searchUrl}?${params.toString()}`, {
                    signal: abortController.signal,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                });
                if (!res.ok) {
                    return;
                }
                const data = await res.json();
                renderResults(data);
            } catch (err) {
                if (err.name !== 'AbortError') {
                }
            } finally {
                searchSpinner?.classList.add('d-none');
                searchIcon?.classList.remove('d-none');
            }
        };

        searchInput.addEventListener('focus', () => {
            openDropdown();
        });

        searchInput.addEventListener('input', () => {
            const hasVal = searchInput.value.trim().length > 0;
            searchClear?.classList.toggle('d-none', !hasVal);
            openDropdown();

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchResults, 120);
        });

        searchClear?.addEventListener('click', () => {
            searchInput.value = '';
            searchClear.classList.add('d-none');
            searchInput.focus();
            fetchResults();
        });

        categoryContainer?.addEventListener('click', (e) => {
            const btn = e.target.closest('.gs-cat-pill');
            if (!btn) {
                return;
            }
            categoryContainer.querySelectorAll('.gs-cat-pill').forEach((el) => el.classList.remove('active'));
            btn.classList.add('active');
            activeCategory = btn.dataset.category || 'all';
            searchInput.focus();
            fetchResults();
        });

        searchResults.addEventListener('click', (e) => {
            const actionChip = e.target.closest('[data-gs-action]');
            if (actionChip) {
                return;
            }
            const item = e.target.closest('.gs-result-item');
            if (item && item.dataset.url) {
                window.location.href = item.dataset.url;
            }
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                openDropdown();
                updateActiveItem(activeIndex + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                openDropdown();
                updateActiveItem(activeIndex - 1);
            } else if (event.key === 'Enter') {
                const items = getSelectableItems();
                const target = activeIndex >= 0 ? items[activeIndex] : items[0];
                if (target && target.dataset.url) {
                    event.preventDefault();
                    window.location.href = target.dataset.url;
                }
            } else if (event.key === 'Escape') {
                closeDropdown();
                searchInput.blur();
            }
        });

        document.addEventListener('click', (event) => {
            if (!searchWrapper.contains(event.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                searchInput.focus();
                searchInput.select();
                openDropdown();
            }
        });
    }

    if (window.jQuery) {
        const $ = window.jQuery;
        const buildSkeletonRows = (colCount, rowCount = 8) => {
            let html = '';
            for (let r = 0; r < rowCount; r++) {
                html += '<tr class="dt-skeleton-row">';
                for (let c = 0; c < colCount; c++) {
                    if (c === 0) {
                        html += '<td><span class="dt-skeleton-bar" style="width: 75%;"></span></td>';
                    } else if (c === 1) {
                        html += '<td><span class="dt-skeleton-bar" style="width: 85%;"></span><span class="dt-skeleton-bar sm"></span></td>';
                    } else if (c === colCount - 1) {
                        html += '<td><span class="dt-skeleton-btn"></span></td>';
                    } else if (c % 3 === 0) {
                        html += '<td><span class="dt-skeleton-pill"></span></td>';
                    } else {
                        const width = 60 + ((r + c) % 3) * 12;
                        html += `<td><span class="dt-skeleton-bar" style="width: ${width}%;"></span></td>`;
                    }
                }
                html += '</tr>';
            }
            return html;
        };

        $(document).on('preXhr.dt', 'table', function () {
            const $table = $(this);
            const $tbody = $table.find('tbody');
            const colCount = $table.find('thead th').length || 6;
            $table.addClass('dt-is-loading');
            $tbody.html(buildSkeletonRows(colCount, 8));
        });

        $(document).on('xhr.dt draw.dt error.dt', 'table', function () {
            $(this).removeClass('dt-is-loading');
        });
    }
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
