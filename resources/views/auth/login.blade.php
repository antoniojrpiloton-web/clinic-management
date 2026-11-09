<x-layouts.guest title="Sign In">
    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label required">Email Address</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    @class(['form-control', 'is-invalid' => $errors->has('email')])
                    placeholder="name@snsu.edu.ph"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label for="password" class="form-label required mb-1">Password</label>
                <a href="{{ route('password.request') }}" class="small text-decoration-none">Forgot password?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="bi bi-lock"></i></span>
                <input
                    type="password"
                    id="password"
                    name="password"
                    @class(['form-control', 'is-invalid' => $errors->has('password')])
                    required
                    autocomplete="current-password"
                >
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    onclick="const p = document.getElementById('password'); const i = this.querySelector('i'); if (p.type === 'password') { p.type = 'text'; i.className = 'bi bi-eye-slash'; } else { p.type = 'password'; i.className = 'bi bi-eye'; }"
                    title="Show / Hide Password"
                >
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
            <label class="form-check-label small" for="remember">Keep me signed in on this device</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mb-3" data-loading-text="Signing in…">
            <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
        </button>

        <div class="position-relative text-center my-3">
            <hr class="text-muted">
            <span class="position-absolute top-50 start-50 translate-middle px-2 bg-white small text-muted">or</span>
        </div>

        <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2">
            <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.616z" fill="#4285F4"/>
                <path d="M9 18c2.43 0 4.467-.806 5.956-2.184l-2.908-2.258c-.806.54-1.836.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332C2.438 15.983 5.482 18 9 18z" fill="#34A853"/>
                <path d="M3.964 10.707c-.18-.54-.282-1.117-.282-1.707s.102-1.167.282-1.707V4.961H.957C.347 6.175 0 7.55 0 9s.347 2.825.957 4.039l3.007-2.332z" fill="#FBBC05"/>
                <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0 5.482 0 2.438 2.017.957 4.961L3.964 7.293C4.672 5.166 6.656 3.58 9 3.58z" fill="#EA4335"/>
            </svg>
            <span>Sign in with Google</span>
        </a>

        <div class="mt-4 pt-3 border-top text-center">
            <p class="small text-muted mb-2">Student, Employee, or Visitor entering the clinic?</p>
            <a href="{{ route('logbook.kiosk') }}" class="btn btn-outline-forest w-100 py-2 fw-semibold">
                <i class="bi bi-journal-check me-1"></i>Open Clinic Visitor Logbook
            </a>
        </div>
    </form>
</x-layouts.guest>
