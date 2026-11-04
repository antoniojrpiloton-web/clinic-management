/**
 * Select2 initialiser for elements marked with data-select2.
 * Requires jQuery + Select2 (loaded by the select2 plugin partial).
 */
document.addEventListener('DOMContentLoaded', () => {
    const $ = window.jQuery;

    document.querySelectorAll('select[data-select2]').forEach((select) => {
        const modal = select.closest('.modal');

        $(select).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: select.dataset.placeholder ?? 'Select an option',
            allowClear: select.hasAttribute('data-allow-clear'),
            closeOnSelect: !select.multiple,
            dropdownParent: modal ? $(modal) : $(document.body),
        });

        // Bubble Select2 changes as native events so vanilla listeners react.
        $(select).on('change.select2', () => select.dispatchEvent(new Event('change', { bubbles: true })));
    });
});

