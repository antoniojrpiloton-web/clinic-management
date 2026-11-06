<x-layouts.app title="Disease & Morbidity Categories">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <span>Configuration</span>
        <span>&gt;</span>
        <span>Disease &amp; Morbidity Categories</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Disease &amp; Morbidity Categories</h1>
            <p class="dashboard-subheading mb-0">Manage database-driven Communicable and Non-Communicable disease classifications used in Consultations and Chart Reports.</p>
        </div>

        @can('medical-consultations.create')
            <button type="button" class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modal-add-disease-category">
                <i class="bi bi-plus-lg me-1"></i> Add Disease Category
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Total DB Disease Categories</span>
                    <span class="kpi-icon-box blue"><i class="bi bi-diagram-3"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($totalCategories) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Communicable Categories</span>
                    <span class="kpi-icon-box green"><i class="bi bi-virus"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($communicableCount) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="kpi-card">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="kpi-label">Non-Communicable Categories</span>
                    <span class="kpi-icon-box amber"><i class="bi bi-heart-pulse"></i></span>
                </div>
                <div class="kpi-value">{{ number_format($nonCommunicableCount) }}</div>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-clipboard2-pulse text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Disease Category Masterlist ({{ number_format($totalCategories) }})</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="disease-categories-table" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Classification</th>
                            <th>Category Name (Chart Label)</th>
                            <th>Auto-Match Keywords</th>
                            <th class="text-center">Linked Cases</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="7" :rows="8" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('medical-consultations.create')
        <div class="modal fade" id="modal-add-disease-category" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form method="POST" action="{{ route('disease-categories.store') }}" novalidate>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Add Disease / Morbidity Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Category Code</label>
                                    <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. COMM-DENGUE, NCD-ENT" required maxlength="40">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label required">Report Classification</label>
                                    <select name="classification" class="form-select" required>
                                        <option value="communicable">Communicable Disease</option>
                                        <option value="non_communicable" selected>Non-Communicable Disease</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Chart Sort Order</label>
                                    <input type="number" name="sort_order" class="form-control" value="50" min="0" max="9999">
                                </div>
                                <div class="col-12">
                                    <label class="form-label required">Category Name (Displayed on Report &amp; Chart)</label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Respiratory (Asthma and Dyspnea)" required maxlength="255">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Diagnosis Keywords (Comma-separated)</label>
                                    <textarea name="keywords" rows="2" class="form-control" placeholder="e.g. asthma, dyspnea, wheezing, shortness of breath"></textarea>
                                    <small class="text-muted">Used to automatically classify free-text diagnoses when no category is explicitly selected.</small>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="add_dc_active" name="is_active" value="1" checked>
                                        <label class="form-check-label" for="add_dc_active">Active in Consultations &amp; Reports</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('medical-consultations.edit')
        <div class="modal fade" id="modal-edit-disease-category" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="form-edit-disease-category" method="POST" action="" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Disease / Morbidity Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label required">Category Code</label>
                                    <input type="text" id="edit_dc_code" name="code" class="form-control text-uppercase" required maxlength="40">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label required">Report Classification</label>
                                    <select id="edit_dc_classification" name="classification" class="form-select" required>
                                        <option value="communicable">Communicable Disease</option>
                                        <option value="non_communicable">Non-Communicable Disease</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Chart Sort Order</label>
                                    <input type="number" id="edit_dc_sort" name="sort_order" class="form-control" min="0" max="9999">
                                </div>
                                <div class="col-12">
                                    <label class="form-label required">Category Name (Displayed on Report &amp; Chart)</label>
                                    <input type="text" id="edit_dc_name" name="name" class="form-control" required maxlength="255">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Diagnosis Keywords (Comma-separated)</label>
                                    <textarea id="edit_dc_keywords" name="keywords" rows="2" class="form-control"></textarea>
                                </div>
                                <div class="col-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="edit_dc_active" name="is_active" value="1">
                                        <label class="form-check-label" for="edit_dc_active">Active in Consultations &amp; Reports</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @include('partials.plugins.datatables')

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const $ = window.jQuery;
                if ($ && $.fn.DataTable) {
                    $('#disease-categories-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('disease-categories.index') }}',
                        pageLength: 25,
                        order: [[1, 'asc']],
                        columns: [
                            { data: 'code_col', name: 'code' },
                            { data: 'classification_col', name: 'classification' },
                            { data: 'name_col', name: 'name' },
                            { data: 'keywords_col', name: 'keywords', orderable: false },
                            { data: 'count_col', name: 'medical_consultations_count', className: 'text-center', searchable: false, orderable: false },
                            { data: 'status_col', name: 'is_active', className: 'text-center', searchable: false },
                            { data: 'actions_col', name: 'actions', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }

                const editModal = document.getElementById('modal-edit-disease-category');
                if (editModal) {
                    editModal.addEventListener('show.bs.modal', function (event) {
                        const button = event.relatedTarget;
                        if (!button) return;

                        document.getElementById('form-edit-disease-category').action = button.getAttribute('data-update-url');
                        document.getElementById('edit_dc_code').value = button.getAttribute('data-category-code') || '';
                        document.getElementById('edit_dc_name').value = button.getAttribute('data-category-name') || '';
                        document.getElementById('edit_dc_classification').value = button.getAttribute('data-category-classification') || 'non_communicable';
                        document.getElementById('edit_dc_keywords').value = button.getAttribute('data-category-keywords') || '';
                        document.getElementById('edit_dc_sort').value = button.getAttribute('data-category-sort') || 0;
                        document.getElementById('edit_dc_active').checked = button.getAttribute('data-category-active') === '1';
                    });
                }
            });
        </script>
    @endpush
</x-layouts.app>

