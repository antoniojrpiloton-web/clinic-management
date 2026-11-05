document.addEventListener('DOMContentLoaded', () => {
    const { Chart } = window;
    const rawData = document.getElementById('dashboard-chart-data')?.textContent;
    const data = JSON.parse(rawData ?? '{}');

    if (!Chart) {
        return;
    }

    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily || "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    const monthlyCanvas = document.getElementById('monthly-utilization-chart');
    if (monthlyCanvas && data.monthly_utilization) {
        const mu = data.monthly_utilization;
        new Chart(monthlyCanvas, {
            type: 'bar',
            data: {
                labels: mu.months,
                datasets: [
                    {
                        label: 'Medical Consults',
                        data: mu.medical,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6,
                    },
                    {
                        label: 'Dental Consults',
                        data: mu.dental,
                        backgroundColor: '#06b6d4',
                        borderRadius: 4,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6,
                    },
                    {
                        label: 'Emergency / First Aid',
                        data: mu.emergency,
                        backgroundColor: '#ef4444',
                        borderRadius: 4,
                        barPercentage: 0.7,
                        categoryPercentage: 0.6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 10,
                            boxHeight: 10,
                            padding: 12,
                            font: { size: 11, weight: '500' },
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
                    x: {
                        stacked: true,
                        grid: { display: false },
                        border: { display: false },
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            callback: (v) => v.toLocaleString(),
                        },
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                    },
                },
            },
        });
    }

    const donutCanvas = document.getElementById('classification-donut-chart');
    if (donutCanvas && data.classification_breakdown) {
        const cb = data.classification_breakdown;
        new Chart(donutCanvas, {
            type: 'doughnut',
            data: {
                labels: cb.labels,
                datasets: [{
                    data: cb.data,
                    backgroundColor: cb.colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            boxHeight: 10,
                            padding: 12,
                            font: { size: 11, weight: '500' },
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
            },
        });
    }

    const servicesCanvas = document.getElementById('services-breakdown-chart');
    if (servicesCanvas && data.services_breakdown) {
        const sb = data.services_breakdown;
        new Chart(servicesCanvas, {
            type: 'polarArea',
            data: {
                labels: sb.labels,
                datasets: [{
                    data: sb.data,
                    backgroundColor: sb.colors.map(c => c + 'CC'),
                    borderColor: sb.colors,
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            boxHeight: 10,
                            padding: 8,
                            font: { size: 10, weight: '500' },
                        },
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    r: {
                        ticks: { display: false },
                        grid: { color: '#f1f5f9' },
                    },
                },
            },
        });
    }

    const complaintsCanvas = document.getElementById('top-complaints-chart');
    if (complaintsCanvas && data.top_complaints) {
        const tc = data.top_complaints;
        new Chart(complaintsCanvas, {
            type: 'bar',
            data: {
                labels: tc.labels,
                datasets: [{
                    label: 'Visits',
                    data: tc.data,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4,
                    barPercentage: 0.65,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            font: { size: 10 },
                        },
                    },
                },
            },
        });
    }

    const fitnessCanvas = document.getElementById('fitness-assessment-chart');
    if (fitnessCanvas && data.pe_fitness_breakdown) {
        const fb = data.pe_fitness_breakdown;
        new Chart(fitnessCanvas, {
            type: 'doughnut',
            data: {
                labels: fb.labels,
                datasets: [{
                    data: fb.data,
                    backgroundColor: fb.colors,
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 8,
                            boxHeight: 8,
                            padding: 6,
                            font: { size: 9, weight: '500' },
                        },
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
            },
        });
    }

    const collegeCanvas = document.getElementById('college-utilization-chart');
    if (collegeCanvas && data.college_utilization) {
        const cu = data.college_utilization;
        new Chart(collegeCanvas, {
            type: 'bar',
            data: {
                labels: cu.labels,
                datasets: [{
                    label: 'Consultations',
                    data: cu.data,
                    backgroundColor: '#0284c7',
                    borderRadius: 4,
                    barPercentage: 0.65,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { font: { size: 10 } },
                    },
                },
            },
        });
    }

    const medicineCanvas = document.getElementById('medicine-utilization-chart');
    if (medicineCanvas && data.top_medicines) {
        const tm = data.top_medicines;
        new Chart(medicineCanvas, {
            type: 'bar',
            data: {
                labels: tm.labels,
                datasets: [{
                    label: 'Units Dispensed',
                    data: tm.quantities,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                    barPercentage: 0.65,
                }],
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: '#f1f5f9' },
                        border: { display: false },
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: {
                            font: { size: 10 },
                        },
                    },
                },
            },
        });
    }
});
