@php
    $isEdit = $role->exists;
    $checked = old('permissions', $assigned);
@endphp

<div class="mb-4">
    <label for="name" class="form-label required">Role Name</label>
    <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name', $role->name) }}"
        @class(['form-control', 'is-invalid' => $errors->has('name')])
        required
    >
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <span class="form-label mb-0">Permissions</span>
    <button type="button" class="btn btn-sm btn-outline-secondary" data-permission-toggle="all">
        Toggle All
    </button>
</div>

<div class="row g-3">
    @foreach ($permissionGroups as $group => $permissions)
        <div class="col-md-6 col-xl-4">
            <div class="permission-group">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <span class="fw-semibold text-capitalize">{{ str_replace('-', ' ', $group) }}</span>
                    <button
                        type="button"
                        class="btn btn-link btn-sm p-0 text-decoration-none"
                        data-permission-toggle="{{ $group }}"
                    >
                        Toggle
                    </button>
                </div>

                @foreach ($permissions as $permission)
                    <div class="form-check mb-1">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            id="perm-{{ $permission->id }}"
                            name="permissions[]"
                            value="{{ $permission->name }}"
                            data-group="{{ $group }}"
                            @checked(in_array($permission->name, $checked, true))
                        >
                        <label class="form-check-label small" for="perm-{{ $permission->id }}">
                            {{ $permission->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<hr class="my-4">

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('roles.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary" data-loading-text="Saving…">
        <i class="bi bi-check-lg me-1"></i>{{ $isEdit ? 'Save Changes' : 'Create Role' }}
    </button>
</div>

@pushOnce('scripts', 'roles-scripts')
    @vite(['resources/js/pages/roles.js'])
@endPushOnce

