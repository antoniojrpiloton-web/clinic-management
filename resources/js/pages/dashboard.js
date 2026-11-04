/**
 * Dashboard charts (Chart.js) styled after the SNSU Executive Intelligence System.
 */
document.addEventListener('DOMContentLoaded', () => {
    const { Chart } = window;
    const data = JSON.parse(document.getElementById('dashboard-chart-data')?.textContent ?? '{}');

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#64748b';

    // 1. Sign-in Activity Overview (SNSU Deep Green & Emerald columns)
    const loginCanvas = document.getElementById('login-trend-chart');

    if (loginCanvas && data.logins) {
        new Chart(loginCanvas, {
            type: 'bar',
            data: {
                labels: data.logins.labels,
                datasets: [{
                    label: 'Sign-ins',
                    data: data.logins.values,
                    backgroundColor: '#005a36',
                    hoverBackgroundColor: '#059669',
                    borderRadius: 4,
                    barThickness: 14,
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: '#f1f5f9' },
                    },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // 2. Role Distribution Doughnut
    const rolesCanvas = document.getElementById('roles-chart');

    if (rolesCanvas && data.roles) {
        new Chart(rolesCanvas, {
            type: 'doughnut',
            data: {
                labels: data.roles.map((role) => role.label),
                datasets: [{
                    data: data.roles.map((role) => role.value),
                    backgroundColor: ['#005a36', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#64748b'],
                    borderWidth: 0,
                }],
            },
            options: {
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            },
        });
    }
});
