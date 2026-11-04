<x-layouts.app title="Dashboard Overview">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <span>Dashboard</span>
    </div>

    <h1 class="dashboard-heading">Dashboard Overview</h1>
    <p class="dashboard-subheading">Welcome back! Here's what's happening with your business today.</p>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Revenue</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-currency-dollar"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $stats['total_revenue'] ?? '$45,231.89' }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-arrow-up-short"></i> 20.1%
                    </span>
                    <span class="trend-compare">vs last month</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Active Users</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-people"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $stats['users_display'] ?? number_format($stats['active_users']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-arrow-up-short"></i> 15.3%
                    </span>
                    <span class="trend-compare">vs last month</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total Orders</span>
                    <span class="kpi-icon-box red">
                        <i class="bi bi-cart"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $stats['orders_display'] ?? number_format($stats['audit_events']) }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge down">
                        <i class="bi bi-arrow-down-short"></i> 4.2%
                    </span>
                    <span class="trend-compare">vs last month</span>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Conversion Rate</span>
                    <span class="kpi-icon-box green">
                        <i class="bi bi-graph-up-arrow"></i>
                    </span>
                </div>
                <div class="kpi-value">{{ $stats['conversion_rate'] ?? '3.42%' }}</div>
                <div class="kpi-trend">
                    <span class="trend-badge up">
                        <i class="bi bi-arrow-up-short"></i> 8.7%
                    </span>
                    <span class="trend-compare">vs last month</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4" id="charts">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Revenue Overview</h5>
                </div>
                <div class="card-body px-4 pt-2 pb-3">
                    <div style="height: 300px; position: relative;">
                        <canvas id="revenue-overview-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Profit vs Expenses</h5>
                </div>
                <div class="card-body px-4 pt-2 pb-3">
                    <div style="height: 300px; position: relative;">
                        <canvas id="profit-expenses-chart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Recent Orders</h5>
                </div>
                <div class="card-body px-4 pt-2">
                    @foreach ($recentOrders as $order)
                        <div class="list-item-clean">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $order['name'] }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $order['item'] }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $order['amount'] }}</div>
                                <div>
                                    <span class="status-badge {{ $order['status'] }}">{{ $order['status'] }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header border-0 pb-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.05rem;">Top Products</h5>
                </div>
                <div class="card-body px-4 pt-2">
                    @foreach ($topProducts as $product)
                        <div class="list-item-clean">
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $product['name'] }}</div>
                                <div class="text-muted" style="font-size: 0.78rem;">{{ $product['sales'] }}</div>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $product['amount'] }}</div>
                                <div class="{{ $product['is_up'] ? 'text-success' : 'text-danger' }}" style="font-size: 0.75rem; font-weight: 600;">
                                    <i class="bi {{ $product['is_up'] ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                    {{ $product['trend'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script id="dashboard-chart-data" type="application/json">@json($charts)</script>

    @pushOnce('scripts', 'chartjs-scripts')
        <script src="{{ asset('assets/plugins/chartjs/chart.umd.min.js') }}"></script>
        @vite(['resources/js/pages/dashboard.js'])
    @endPushOnce
</x-layouts.app>
