@php
    $isEdit = $user->exists;
    $selectedRoles = old('roles', $isEdit ? $user->roles->pluck('name')->all() : []);
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label required">Full Name</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $user->name) }}"
            @class(['form-control', 'is-invalid' => $errors->has('name')])
            required
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="email" class="form-label required">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $user->email) }}"
            @class(['form-control', 'is-invalid' => $errors->has('email')])
            required
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="phone" class="form-label">Phone Number</label>
        <input
            type="text"
            id="phone"
            name="phone"
            value="{{ old('phone', $user->phone) }}"
            @class(['form-control', 'is-invalid' => $errors->has('phone')])
        >
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="roles" class="form-label required">Assigned Roles</label>
        <select
            id="roles"
            name="roles[]"
            multiple
            data-select2
            data-ajax-url="{{ route('select2.roles') }}"
            data-placeholder="Select one or more roles"
            @class(['form-select', 'is-invalid' => $errors->has('roles') || $errors->has('roles.*')])
            required
        >
            @foreach ($selectedRoles as $roleName)
                <option value="{{ $roleName }}" selected>{{ $roleName }}</option>
            @endforeach
        </select>
        @error('roles')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password" @class(['form-label', 'required' => ! $isEdit])>
            {{ $isEdit ? 'New Password (leave blank to keep current)' : 'Password' }}
        </label>
        <input
            type="password"
            id="password"
            name="password"
            @class(['form-control', 'is-invalid' => $errors->has('password')])
            @required(! $isEdit)
            autocomplete="new-password"
        >
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="password_confirmation" @class(['form-label', 'required' => ! $isEdit])>Confirm Password</label>
        <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            class="form-control"
            @required(! $isEdit)
            autocomplete="new-password"
        >
    </div>

    <div class="col-12">
        <div class="form-check form-switch">
            <input
                class="form-check-input"
                type="checkbox"
                role="switch"
                id="is_active"
                name="is_active"
                value="1"
                @checked(old('is_active', $user->is_active))
            >
            <label class="form-check-label" for="is_active">Account is active and allowed to sign in</label>
        </div>
        @error('is_active')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>

<hr class="my-4">

<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('users.index') }}" class="btn btn-light">Cancel</a>
    <button type="submit" class="btn btn-primary" data-loading-text="Saving…">
        <i class="bi bi-check-lg me-1"></i>{{ $isEdit ? 'Save Changes' : 'Create User' }}
    </button>
</div>

@include('partials.plugins.select2')

