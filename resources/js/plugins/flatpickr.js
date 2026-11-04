/**
 * Flatpickr initialiser for inputs marked with data-flatpickr.
 * Options: data-enable-time, data-min-date, data-max-date.
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-flatpickr]').forEach((input) => {
        const enableTime = input.hasAttribute('data-enable-time');

        window.flatpickr(input, {
            enableTime,
            dateFormat: enableTime ? 'Y-m-d H:i' : 'Y-m-d',
            altInput: true,
            altFormat: enableTime ? 'M j, Y h:i K' : 'M j, Y',
            minDate: input.dataset.minDate,
            maxDate: input.dataset.maxDate,
            allowInput: true,
            onChange: () => input.dispatchEvent(new Event('change', { bubbles: true })),
        });
    });
});

