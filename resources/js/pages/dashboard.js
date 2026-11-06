document.addEventListener('DOMContentLoaded', () => {
    const kpiContainer = document.getElementById('dashboard-kpi-cards');
    const metricsUrl = kpiContainer?.dataset.metricsUrl;
    const syncStatusEl = document.getElementById('dashboard-sync-status');
    const refreshBtn = document.getElementById('btn-refresh-dashboard');
    const chartInstances = {};

    if (!metricsUrl) {
        return;
    }

    const fmt = (val) => Number(val ?? 0).toLocaleString();

    const setKpiHtml = (key, html) => {
        document.querySelectorAll(`[data-kpi="${key}"]`).forEach((el) => {
            el.innerHTML = html;
        });
    };

    const setLoadingState = () => {
        if (syncStatusEl) {
            syncStatusEl.className = 'badge badge-soft-blue d-inline-flex align-items-center gap-1 py-1 px-2';
            syncStatusEl.innerHTML = '<span class="spinner-border spinner-border-sm" style="width: 0.7rem; height: 0.7rem;" role="status"></span><span>Syncing live clinic data...</span>';
        }
        if (refreshBtn) {
            refreshBtn.disabled = true;
        }
        document.querySelectorAll('.chart-loader-overlay').forEach((overlay) => {
            overlay.classList.remove('is-loaded');
        });
    };

    const renderCharts = (data) => {
        const { Chart } = window;
        if (!Chart || !data) {
            return;
        }

        Chart.defaults.font.family = getComputedStyle(document.body).fontFamily || "'Inter', sans-serif";
        Chart.defaults.color = '#64748b';

        const mountChart = (canvasId, config) => {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                return;
            }
            if (chartInstances[canvasId]) {
                chartInstances[canvasId].destroy();
            }
            chartInstances[canvasId] = new Chart(canvas, config);
            const overlay = canvas.parentElement?.querySelector('.chart-loader-overlay');
            if (overlay) {
                overlay.classList.add('is-loaded');
            }
        };

        if (data.monthly_utilization) {
            const mu = data.monthly_utilization;
            mountChart('monthly-utilization-chart', {
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

        if (data.classification_breakdown) {
            const cb = data.classification_breakdown;
            mountChart('classification-donut-chart', {
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

        if (data.services_breakdown) {
            const sb = data.services_breakdown;
            mountChart('services-breakdown-chart', {
                type: 'polarArea',
                data: {
                    labels: sb.labels,
                    datasets: [{
                        data: sb.data,
                        backgroundColor: sb.colors.map((c) => `${c}CC`),
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

        if (data.top_complaints) {
            const tc = data.top_complaints;
            mountChart('top-complaints-chart', {
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
                            ticks: { font: { size: 10 } },
                        },
                    },
                },
            });
        }

        if (data.pe_fitness_breakdown) {
            const fb = data.pe_fitness_breakdown;
            mountChart('fitness-assessment-chart', {
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

        if (data.college_utilization) {
            const cu = data.college_utilization;
            mountChart('college-utilization-chart', {
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

        if (data.top_medicines) {
            const tm = data.top_medicines;
            mountChart('medicine-utilization-chart', {
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
                            ticks: { font: { size: 10 } },
                        },
                    },
                },
            });
        }
    };

    const loadDashboardMetrics = async (forceRefresh = false) => {
        setLoadingState();

        try {
            const url = forceRefresh ? `${metricsUrl}?refresh=1` : metricsUrl;
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            const stats = payload.stats ?? {};
            const charts = payload.charts ?? {};
            const sections = payload.sections ?? {};

            setKpiHtml('total_visits', fmt(stats.total_visits));
            setKpiHtml('today_visits_badge', `<i class="bi bi-calendar-check"></i> ${fmt(stats.today_visits)} today`);
            setKpiHtml('this_month_visits_label', `${fmt(stats.this_month_visits)} this month`);

            setKpiHtml('total_patients', fmt(stats.total_patients));
            setKpiHtml('students_count_badge', `<i class="bi bi-mortarboard"></i> ${fmt(stats.students_count)} students`);
            setKpiHtml('employees_count_label', `${fmt(stats.employees_count)} employees`);

            setKpiHtml('total_pe', fmt(stats.total_pe));
            setKpiHtml('completed_pe_badge', `<i class="bi bi-check2-circle"></i> ${fmt(stats.completed_pe)} completed`);

            setKpiHtml('medicines_dispensed', `${fmt(stats.medicines_dispensed)} <small class="fs-6 text-muted fw-normal">units</small>`);
            setKpiHtml('dispensing_orders_badge', `<i class="bi bi-boxes"></i> ${fmt(stats.dispensing_orders)} orders`);

            setKpiHtml('medical_consultations_count', fmt(stats.medical_consultations_count));
            setKpiHtml('dental_consultations_count', fmt(stats.dental_consultations_count));
            setKpiHtml('emergency_records_count', fmt(stats.emergency_records_count));
            setKpiHtml('first_aid_records_count', fmt(stats.first_aid_records_count));
            setKpiHtml('pe_schedules_count', fmt(stats.pe_schedules_count));
            setKpiHtml('inventory_transactions_count', fmt(stats.inventory_transactions_count));

            const commCount = charts.disease_classification?.communicable_count ?? 0;
            const nonCommCount = charts.disease_classification?.non_communicable_count ?? 0;
            setKpiHtml('communicable_badge', `${fmt(commCount)} total cases`);
            setKpiHtml('non_communicable_badge', `${fmt(nonCommCount)} total cases`);

            const sectionMap = {
                'dashboard-alerts-container': sections.alerts,
                'communicable-morbidity-body': sections.communicable,
                'non-communicable-morbidity-body': sections.non_communicable,
                'recent-visits-body': sections.recent_visits,
                'recent-pe-body': sections.recent_pe,
                'upcoming-schedules-body': sections.upcoming_schedules,
                'recent-dispensings-body': sections.recent_dispensings,
                'recent-medical-body': sections.recent_medical,
                'recent-dental-body': sections.recent_dental,
                'recent-emergency-body': sections.recent_emergency,
                'recent-first-aid-body': sections.recent_first_aid,
                'recent-audit-body': sections.recent_audit,
            };

            Object.entries(sectionMap).forEach(([id, html]) => {
                const el = document.getElementById(id);
                if (el && typeof html === 'string') {
                    el.innerHTML = html;
                }
            });

            renderCharts(charts);

            if (syncStatusEl) {
                syncStatusEl.className = 'badge badge-soft-green d-inline-flex align-items-center gap-1 py-1 px-2';
                syncStatusEl.innerHTML = `<i class="bi bi-check-circle-fill"></i><span>Live • Updated ${payload.generated_at ?? ''}</span>`;
            }
        } catch (err) {
            if (syncStatusEl) {
                syncStatusEl.className = 'badge badge-soft-red d-inline-flex align-items-center gap-1 py-1 px-2';
                syncStatusEl.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i><span>Failed to load metrics</span>';
            }
        } finally {
            if (refreshBtn) {
                refreshBtn.disabled = false;
            }
        }
    };

    refreshBtn?.addEventListener('click', () => {
        loadDashboardMetrics(true);
    });

    loadDashboardMetrics(false);
});
