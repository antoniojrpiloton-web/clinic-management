<x-layouts.guest title="Forgot Password" heading="Reset Password" subheading="Enter your email address and we will send you a password reset link">
    <form method="POST" action="{{ route('password.email') }}" novalidate>
        @csrf

        <div class="mb-4">
            <label for="email" class="form-label required">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    @class(['form-control', 'is-invalid' => $errors->has('email')])
                    required
                    autofocus
                >
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" data-loading-text="Sending…">
            Send Password Reset Link
        </button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="small text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Sign In
            </a>
        </div>
    </form>
</x-layouts.guest>

