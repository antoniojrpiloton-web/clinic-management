<x-layouts.guest title="Choose a New Password" heading="Choose a New Password" subheading="Enter and confirm your new password below">
    <form method="POST" action="{{ route('password.store') }}" novalidate>
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label for="email" class="form-label required">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email', $email) }}"
                @class(['form-control', 'is-invalid' => $errors->has('email')])
                required
                autocomplete="username"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label required">New Password</label>
            <input
                type="password"
                id="password"
                name="password"
                @class(['form-control', 'is-invalid' => $errors->has('password')])
                required
                autocomplete="new-password"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label required">Confirm Password</label>
            <input
                type="password"
                id="password_confirmation"
                name="password_confirmation"
                class="form-control"
                required
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2" data-loading-text="Saving…">
            Reset Password
        </button>
    </form>
</x-layouts.guest>

