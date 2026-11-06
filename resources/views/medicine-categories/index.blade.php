<x-layouts.app title="Medicine Categories">
    <div class="breadcrumb-nav">
        <i class="bi bi-house"></i>
        <span>&gt;</span>
        <a href="{{ route('dashboard') }}">Dashboard</a>
        <span>&gt;</span>
        <a href="{{ route('medicines.index') }}">Pharmacy &amp; Inventory</a>
        <span>&gt;</span>
        <span>Medicine Categories</span>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="dashboard-heading mb-1">Medicine Categories</h1>
            <p class="dashboard-subheading mb-0">Manage pharmaceutical classifications, therapeutic categories, and drug groupings.</p>
        </div>

        @can('medicine-categories.create')
            <button type="button" class="btn btn-primary btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#modal-add-category">
                <i class="bi bi-plus-lg me-1"></i> Add Category
            </button>
        @endcan
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Validation error:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border">
        <div class="card-header bg-white py-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-tags text-primary fs-5"></i>
                    <span class="fw-bold text-dark">Category Directory ({{ number_format($totalCategories) }})</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="medicine-categories-table" class="table table-hover align-middle w-100 mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th class="text-center">Medicines Count</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <x-table-skeleton :cols="6" :rows="6" />
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('medicine-categories.create')
        <div class="modal fade" id="modal-add-category" tabindex="-1" aria-labelledby="modalAddCategoryLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('medicine-categories.store') }}" novalidate>
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAddCategoryLabel">Add Medicine Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Category Code</label>
                                <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. ANALGESIC, ANTIBIOTIC, VITAMIN" required maxlength="30">
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Category Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. Analgesics & Antipyretics" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Therapeutic purpose and common uses"></textarea>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="add_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="add_is_active">Active</label>
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

    @can('medicine-categories.edit')
        <div class="modal fade" id="modal-edit-category" tabindex="-1" aria-labelledby="modalEditCategoryLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="form-edit-category" method="POST" action="" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalEditCategoryLabel">Edit Medicine Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label required">Category Code</label>
                                <input type="text" id="edit_code" name="code" class="form-control text-uppercase" required maxlength="30">
                            </div>
                            <div class="mb-3">
                                <label class="form-label required">Category Name</label>
                                <input type="text" id="edit_name" name="name" class="form-control" required maxlength="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea id="edit_description" name="description" rows="3" class="form-control"></textarea>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="edit_is_active" name="is_active" value="1">
                                <label class="form-check-label" for="edit_is_active">Active</label>
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
                    $('#medicine-categories-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('medicine-categories.index') }}',
                        pageLength: 25,
                        order: [[1, 'asc']],
                        columns: [
                            { data: 'code_col', name: 'code' },
                            { data: 'name_col', name: 'name' },
                            { data: 'desc_col', name: 'description', orderable: false },
                            { data: 'count_col', name: 'medicines_count', className: 'text-center', searchable: false },
                            { data: 'status_col', name: 'is_active', className: 'text-center', searchable: false },
                            { data: 'actions_col', orderable: false, searchable: false, className: 'text-end' }
                        ]
                    });
                }

                const editModalEl = document.getElementById('modal-edit-category');
                if (!editModalEl) return;

                editModalEl.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;
                    const updateUrl = btn.getAttribute('data-update-url');
                    const form = document.getElementById('form-edit-category');
                    form.action = updateUrl;

                    document.getElementById('edit_name').value = btn.getAttribute('data-category-name') || '';
                    document.getElementById('edit_code').value = btn.getAttribute('data-category-code') || '';
                    document.getElementById('edit_description').value = btn.getAttribute('data-category-description') || '';
                    document.getElementById('edit_is_active').checked = btn.getAttribute('data-category-active') === '1';
                });
            });
        </script>
    @endpush
</x-layouts.app>

