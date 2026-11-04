<x-layouts.app title="My Profile">
    <x-page-header title="My Profile" :breadcrumbs="['My Profile' => route('profile.edit')]" />

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card text-center">
                <div class="card-body py-4">
                    <span class="avatar avatar-lg mb-3">{{ $user->initials() }}</span>
                    <h2 class="h5 fw-semibold mb-1">{{ $user->name }}</h2>
                    <div class="text-muted small mb-3">{{ $user->email }}</div>
                    <div>
                        @foreach ($user->roles as $role)
                            <span class="badge rounded-pill text-bg-primary">{{ $role->name }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">Account Details</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" novalidate>
                        @csrf
                        @method('PUT')

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
                        </div>

                        <hr class="my-4">
                        <h3 class="h6 fw-semibold mb-3">Change Password <small class="text-muted fw-normal">(optional)</small></h3>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input
                                    type="password"
                                    id="current_password"
                                    name="current_password"
                                    @class(['form-control', 'is-invalid' => $errors->has('current_password')])
                                    autocomplete="current-password"
                                >
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="password" class="form-label">New Password</label>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    @class(['form-control', 'is-invalid' => $errors->has('password')])
                                    autocomplete="new-password"
                                >
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="form-control"
                                    autocomplete="new-password"
                                >
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary" data-loading-text="Saving…">
                                <i class="bi bi-check-lg me-1"></i>Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

