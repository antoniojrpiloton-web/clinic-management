/**
 * Role permission matrix helpers (select/deselect all or by module group).
 */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-permission-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const group = button.dataset.permissionToggle;
            const selector = group === 'all'
                ? 'input[name="permissions[]"]'
                : `input[name="permissions[]"][data-group="${group}"]`;

            const boxes = [...document.querySelectorAll(selector)];
            const shouldCheck = boxes.some((box) => !box.checked);

            boxes.forEach((box) => {
                box.checked = shouldCheck;
            });
        });
    });
});

