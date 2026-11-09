document.addEventListener('DOMContentLoaded', () => {
    const $ = window.jQuery;
    if (!$) return;

    function initSelect2(context) {
        const root = context || document;
        root.querySelectorAll('select[data-select2], select[data-ajax-url], select.select2').forEach((select) => {
            const modal = select.closest('.modal');
            const ajaxUrl = select.dataset.ajaxUrl;
            const placeholder = select.dataset.placeholder ?? 'Select an option';
            const allowClear = select.hasAttribute('data-allow-clear');
            const dependsOn = select.dataset.dependsOn;
            const dependsParam = select.dataset.dependsParam;

            if ($(select).hasClass('select2-hidden-accessible')) {
                $(select).select2('destroy');
            }

            const options = {
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: placeholder,
                allowClear: allowClear,
                closeOnSelect: !select.multiple,
            };

            if (modal) {
                options.dropdownParent = $(modal);
            }

            if (ajaxUrl) {
                options.ajax = {
                    url: ajaxUrl,
                    dataType: 'json',
                    delay: 250,
                    cache: true,
                    data: (params) => {
                        const payload = {
                            q: params.term || '',
                            page: params.page || 1,
                        };

                        if (dependsOn && dependsParam) {
                            const depEl = document.querySelector(dependsOn);
                            if (depEl) {
                                payload[dependsParam] = depEl.value;
                            }
                        }

                        return payload;
                    },
                    processResults: (data, params) => {
                        params.page = params.page || 1;
                        return {
                            results: data.results || [],
                            pagination: {
                                more: data.pagination ? data.pagination.more : false,
                            },
                        };
                    },
                };

                options.templateResult = (item) => {
                    if (!item || !item.id) {
                        return item ? item.text : '';
                    }
                    const container = document.createElement('div');
                    container.className = 'py-0';
                    const title = document.createElement('div');
                    title.className = 'fw-medium text-dark';
                    title.textContent = item.text || '';
                    container.appendChild(title);

                    if (item.subtext) {
                        const sub = document.createElement('small');
                        sub.className = 'text-muted d-block';
                        sub.style.fontSize = '0.72rem';
                        sub.textContent = item.subtext;
                        container.appendChild(sub);
                    }

                    return $(container);
                };

                options.templateSelection = (item) => {
                    return (item && (item.text || item.id)) || placeholder;
                };
            }

            $(select).select2(options);

            $(select).on('change.select2', () => {
                select.dispatchEvent(new Event('change', { bubbles: true }));
            });

            if (dependsOn) {
                const parentEl = document.querySelector(dependsOn);
                if (parentEl) {
                    parentEl.addEventListener('change', () => {
                        $(select).val(null).trigger('change');
                    });
                }
            }
        });
    }

    initSelect2(document);

    document.querySelectorAll('.modal').forEach((modalEl) => {
        modalEl.addEventListener('shown.bs.modal', () => {
            initSelect2(modalEl);
        });
    });
});
