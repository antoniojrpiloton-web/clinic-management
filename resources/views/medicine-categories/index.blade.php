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
                    <span class="fw-bold text-dark">Category Directory ({{ $categories->count() }})</span>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Code</th>
                        <th>Category Name</th>
                        <th>Description</th>
                        <th class="text-center">Medicines Count</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $cat)
                        <tr>
                            <td class="ps-3">
                                <span class="badge badge-soft-blue font-monospace">{{ $cat->code }}</span>
                            </td>
                            <td class="fw-bold text-dark">
                                {{ $cat->name }}
                            </td>
                            <td class="text-muted small">
                                {{ $cat->description ?: 'N/A' }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('medicines.index', ['category_id' => $cat->id]) }}" class="badge badge-soft-gray text-decoration-none">
                                    {{ $cat->medicines_count }} items
                                </a>
                            </td>
                            <td class="text-center">
                                @if($cat->is_active)
                                    <span class="badge badge-soft-green"><i class="bi bi-check-circle me-1"></i>Active</span>
                                @else
                                    <span class="badge badge-soft-gray"><i class="bi bi-pause-circle me-1"></i>Inactive</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    @can('medicine-categories.edit')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light border p-1 px-2"
                                            title="Edit Category"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modal-edit-category"
                                            data-category-id="{{ $cat->id }}"
                                            data-category-name="{{ $cat->name }}"
                                            data-category-code="{{ $cat->code }}"
                                            data-category-description="{{ $cat->description }}"
                                            data-category-active="{{ $cat->is_active ? '1' : '0' }}"
                                            data-update-url="{{ route('medicine-categories.update', $cat) }}"
                                        >
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endcan

                                    @can('medicine-categories.delete')
                                        <form method="POST" action="{{ route('medicine-categories.destroy', $cat) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to remove category {{ $cat->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger p-1 px-2" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No medicine categories created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
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

