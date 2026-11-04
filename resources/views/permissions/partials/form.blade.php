<div class="mb-3">
    <label for="name" class="form-label required">Permission Key</label>
    <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name', $permission->name) }}"
        placeholder="e.g. patients.view"
        @class(['form-control', 'is-invalid' => $errors->has('name')])
        required
    >
    <div class="form-text">Use the lowercase <code>module.action</code> convention (for example <code>patients.view</code>).</div>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr class="my-4">

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('permissions.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary" data-loading-text="Saving…">
        <i class="bi bi-check-lg me-1"></i>{{ $permission->exists ? 'Save Changes' : 'Create Permission' }}
    </button>
</div>

