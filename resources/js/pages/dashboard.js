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

    const isDark = () => document.documentElement.getAttribute('data-bs-theme') === 'dark' || document.documentElement.getAttribute('data-theme') === 'dark';
    const getThemeMode = () => (isDark() ? 'dark' : 'light');
    const getGridColor = () => (isDark() ? '#363853' : 'rgba(67, 89, 113, 0.1)');
    const getLabelColor = () => (isDark() ? '#a3a4cc' : '#566a7f');
    const getHeadingColor = () => (isDark() ? '#dbdcff' : '#566a7f');

    const sneatPalette = ['#696cff', '#03c3ec', '#71dd37', '#ffab00', '#ff3e1d', '#8592a3', '#5f61e6'];

    const renderCharts = (data) => {
        const { ApexCharts } = window;
        if (!ApexCharts || !data) {
            return;
        }

        const mountChart = (containerId, options) => {
            const container = document.getElementById(containerId);
            if (!container) {
                return;
            }
            if (chartInstances[containerId]) {
                chartInstances[containerId].destroy();
            }
            const chart = new ApexCharts(container, options);
            chart.render();
            chartInstances[containerId] = chart;
            const overlay = container.parentElement?.querySelector('.chart-loader-overlay');
            if (overlay) {
                overlay.classList.add('is-loaded');
            }
        };

        if (data.monthly_utilization) {
            const mu = data.monthly_utilization;
            mountChart('monthly-utilization-chart', {
                chart: {
                    type: 'area',
                    height: 310,
                    toolbar: { show: false },
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                },
                series: [
                    { name: 'Medical Consults', data: mu.medical || [] },
                    { name: 'Dental Consults', data: mu.dental || [] },
                    { name: 'Emergency / First Aid', data: mu.emergency || [] },
                ],
                colors: ['#696cff', '#03c3ec', '#71dd37'],
                stroke: {
                    curve: 'smooth',
                    width: 2.5,
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.42,
                        opacityTo: 0.05,
                        stops: [0, 95, 100],
                    },
                },
                dataLabels: { enabled: false },
                legend: {
                    position: 'top',
                    horizontalAlign: 'right',
                    fontSize: '11px',
                    labels: { colors: getLabelColor() },
                    markers: { radius: 12, width: 9, height: 9 },
                    itemMargin: { horizontal: 8 },
                },
                grid: {
                    borderColor: getGridColor(),
                    strokeDashArray: 4,
                    padding: { top: 0, right: 10, bottom: 0, left: 10 },
                },
                xaxis: {
                    categories: mu.months || [],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { style: { colors: getLabelColor(), fontSize: '11px' } },
                },
                yaxis: {
                    labels: {
                        style: { colors: getLabelColor(), fontSize: '11px' },
                        formatter: (val) => Number(val).toLocaleString(),
                    },
                },
                tooltip: {
                    theme: getThemeMode(),
                },
            });
        }

        if (data.classification_breakdown) {
            const cb = data.classification_breakdown;
            const total = (cb.data || []).reduce((a, b) => a + Number(b), 0);
            mountChart('classification-donut-chart', {
                chart: {
                    type: 'donut',
                    height: 240,
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                },
                series: (cb.data || []).map((v) => Number(v)),
                labels: cb.labels || ['Students', 'Faculty', 'Staff', 'Others'],
                colors: ['#696cff', '#03c3ec', '#71dd37', '#ffab00'],
                stroke: { width: 4, colors: [isDark() ? '#2b2c40' : '#ffffff'] },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '12px',
                                    fontFamily: 'Inter, sans-serif',
                                    color: getLabelColor(),
                                    offsetY: -4,
                                },
                                value: {
                                    fontSize: '18px',
                                    fontFamily: 'Inter, sans-serif',
                                    fontWeight: 700,
                                    color: getHeadingColor(),
                                    offsetY: 4,
                                    formatter: (val) => Number(val).toLocaleString(),
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px',
                                    fontFamily: 'Inter, sans-serif',
                                    color: getLabelColor(),
                                    formatter: () => total.toLocaleString(),
                                },
                            },
                        },
                    },
                },
                legend: {
                    position: 'bottom',
                    fontSize: '11px',
                    labels: { colors: getLabelColor() },
                    markers: { radius: 12, width: 8, height: 8 },
                    itemMargin: { horizontal: 6 },
                },
                tooltip: { theme: getThemeMode() },
            });
        }

        if (data.services_breakdown) {
            const sb = data.services_breakdown;
            const total = (sb.data || []).reduce((a, b) => a + Number(b), 0);
            mountChart('services-breakdown-chart', {
                chart: {
                    type: 'donut',
                    height: 250,
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                },
                series: (sb.data || []).map((v) => Number(v)),
                labels: sb.labels || ['Medical', 'Dental', 'Emergency', 'First Aid', 'General'],
                colors: ['#696cff', '#03c3ec', '#ff3e1d', '#ffab00', '#71dd37'],
                stroke: { width: 4, colors: [isDark() ? '#2b2c40' : '#ffffff'] },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '11px',
                                    color: getLabelColor(),
                                    offsetY: -4,
                                },
                                value: {
                                    fontSize: '17px',
                                    fontWeight: 700,
                                    color: getHeadingColor(),
                                    offsetY: 4,
                                    formatter: (val) => Number(val).toLocaleString(),
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px',
                                    color: getLabelColor(),
                                    formatter: () => total.toLocaleString(),
                                },
                            },
                        },
                    },
                },
                legend: {
                    position: 'bottom',
                    fontSize: '10px',
                    labels: { colors: getLabelColor() },
                    markers: { radius: 12, width: 8, height: 8 },
                    itemMargin: { horizontal: 5 },
                },
                tooltip: { theme: getThemeMode() },
            });
        }

        if (data.top_complaints) {
            const tc = data.top_complaints;
            mountChart('top-complaints-chart', {
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: { show: false },
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                },
                series: [{ name: 'Visits', data: tc.data || [] }],
                colors: sneatPalette,
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5,
                        barHeight: '48%',
                        distributed: true,
                    },
                },
                legend: { show: false },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: getGridColor(),
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                },
                xaxis: {
                    categories: tc.labels || [],
                    labels: {
                        style: { colors: getLabelColor(), fontSize: '10px' },
                        formatter: (val) => Number(val).toLocaleString(),
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: { style: { colors: getLabelColor(), fontSize: '10px' } },
                },
                tooltip: { theme: getThemeMode() },
            });
        }

        if (data.pe_fitness_breakdown) {
            const fb = data.pe_fitness_breakdown;
            const total = (fb.data || []).reduce((a, b) => a + Number(b), 0);
            mountChart('fitness-assessment-chart', {
                chart: {
                    type: 'donut',
                    height: 250,
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                },
                series: (fb.data || []).map((v) => Number(v)),
                labels: fb.labels || ['Class A', 'Class B', 'Class C', 'Class D', 'Class E'],
                colors: ['#71dd37', '#03c3ec', '#696cff', '#ffab00', '#ff3e1d'],
                stroke: { width: 4, colors: [isDark() ? '#2b2c40' : '#ffffff'] },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '72%',
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '11px',
                                    color: getLabelColor(),
                                    offsetY: -4,
                                },
                                value: {
                                    fontSize: '17px',
                                    fontWeight: 700,
                                    color: getHeadingColor(),
                                    offsetY: 4,
                                    formatter: (val) => Number(val).toLocaleString(),
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '11px',
                                    color: getLabelColor(),
                                    formatter: () => total.toLocaleString(),
                                },
                            },
                        },
                    },
                },
                legend: {
                    position: 'bottom',
                    fontSize: '9px',
                    labels: { colors: getLabelColor() },
                    markers: { radius: 12, width: 8, height: 8 },
                    itemMargin: { horizontal: 4 },
                },
                tooltip: { theme: getThemeMode() },
            });
        }

        if (data.college_utilization) {
            const cu = data.college_utilization;
            mountChart('college-utilization-chart', {
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: { show: false },
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                },
                series: [{ name: 'Consultations', data: cu.data || [] }],
                colors: sneatPalette,
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5,
                        barHeight: '48%',
                        distributed: true,
                    },
                },
                legend: { show: false },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: getGridColor(),
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                },
                xaxis: {
                    categories: cu.labels || [],
                    labels: {
                        style: { colors: getLabelColor(), fontSize: '10px' },
                        formatter: (val) => Number(val).toLocaleString(),
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: { style: { colors: getLabelColor(), fontSize: '10px' } },
                },
                tooltip: { theme: getThemeMode() },
            });
        }

        if (data.top_medicines) {
            const tm = data.top_medicines;
            mountChart('medicine-utilization-chart', {
                chart: {
                    type: 'bar',
                    height: 250,
                    toolbar: { show: false },
                    parentHeightOffset: 0,
                    fontFamily: 'Inter, sans-serif',
                },
                series: [{ name: 'Units Dispensed', data: tm.quantities || [] }],
                colors: sneatPalette,
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5,
                        barHeight: '48%',
                        distributed: true,
                    },
                },
                legend: { show: false },
                dataLabels: { enabled: false },
                grid: {
                    borderColor: getGridColor(),
                    strokeDashArray: 4,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } },
                },
                xaxis: {
                    categories: tm.labels || [],
                    labels: {
                        style: { colors: getLabelColor(), fontSize: '10px' },
                        formatter: (val) => Number(val).toLocaleString(),
                    },
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                },
                yaxis: {
                    labels: { style: { colors: getLabelColor(), fontSize: '10px' } },
                },
                tooltip: { theme: getThemeMode() },
            });
        }
    };

    window.addEventListener('clinic:theme-changed', () => {
        Object.values(chartInstances).forEach((chart) => {
            if (chart && typeof chart.updateOptions === 'function') {
                chart.updateOptions({
                    theme: { mode: getThemeMode() },
                    grid: { borderColor: getGridColor() },
                    xaxis: { labels: { style: { colors: getLabelColor() } } },
                    yaxis: { labels: { style: { colors: getLabelColor() } } },
                    legend: { labels: { colors: getLabelColor() } },
                });
            }
        });
    });

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
