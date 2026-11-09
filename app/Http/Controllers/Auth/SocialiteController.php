<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class SocialiteController extends Controller
{
    public function __construct(protected AuditLogger $auditLogger) {}

    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Unable to sign in with Google. Please try again.',
            ]);
        }

        $email = $googleUser->getEmail();

        if (! $email) {
            return redirect()->route('login')->withErrors([
                'email' => 'No email address provided by Google.',
            ]);
        }

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            if (! $user->is_active) {
                return redirect()->route('login')->withErrors([
                    'email' => 'Your account is deactivated. Please contact the administrator.',
                ]);
            }

            $user->update([
                'google_id' => $user->google_id ?? $googleUser->getId(),
                'avatar' => $user->avatar ?? $googleUser->getAvatar(),
            ]);
        } else {

            $user = User::create([
                'name' => $googleUser->getName() ?: 'Google User',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            $superAdminEmails = [
                'apiloton1@snsu.edu.ph',
                'alpiloton@adssu.edu.ph',
                'admin@clinic.test',
            ];

            if (in_array(strtolower($email), $superAdminEmails, true)) {
                $user->assignRole(User::SUPER_ADMIN_ROLE);
            } else {
                $defaultRole = 'Receptionist';
                if (Role::where('name', $defaultRole)->exists()) {
                    $user->assignRole($defaultRole);
                }
            }

            $this->auditLogger->log('created', "New user registered via Google: {$user->name}", $user);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        $this->auditLogger->log('login', "{$user->name} signed in via Google", $user);

        return redirect()->intended(route('dashboard'));
    }
}
