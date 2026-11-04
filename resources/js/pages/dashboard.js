document.addEventListener('DOMContentLoaded', () => {
    const { Chart } = window;
    const rawData = document.getElementById('dashboard-chart-data')?.textContent;
    const data = JSON.parse(rawData ?? '{}');

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.color = '#94a3b8';

    const revenueCanvas = document.getElementById('revenue-overview-chart');
    if (revenueCanvas && data.months) {
        const ctx = revenueCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 260);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.3)');
        gradient.addColorStop(0.8, 'rgba(59, 130, 246, 0.05)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels: data.months,
                datasets: [{
                    label: 'Revenue',
                    data: data.revenue_overview ?? [],
                    borderColor: '#3b82f6',
                    borderWidth: 2.5,
                    fill: true,
                    backgroundColor: gradient,
                    tension: 0.45,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: '#2563eb',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 6000,
                        ticks: {
                            stepSize: 1500,
                            callback: (value) => value.toLocaleString(),
                        },
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                    },
                },
            },
        });
    }

    const profitCanvas = document.getElementById('profit-expenses-chart');
    if (profitCanvas && data.months) {
        new Chart(profitCanvas, {
            type: 'bar',
            data: {
                labels: data.months,
                datasets: [
                    {
                        label: 'expenses',
                        data: data.expenses ?? [],
                        backgroundColor: '#475569',
                        borderRadius: 2,
                        barPercentage: 0.65,
                        categoryPercentage: 0.65,
                    },
                    {
                        label: 'profit',
                        data: data.profit ?? [],
                        backgroundColor: '#2563eb',
                        borderRadius: 2,
                        barPercentage: 0.65,
                        categoryPercentage: 0.65,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        align: 'center',
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            padding: 15,
                            color: '#334155',
                            font: { size: 12, weight: '500' },
                        },
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleColor: '#ffffff',
                        bodyColor: '#e2e8f0',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 10000,
                        ticks: {
                            stepSize: 2500,
                            callback: (value) => value.toLocaleString(),
                        },
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                    },
                },
            },
        });
    }
});
