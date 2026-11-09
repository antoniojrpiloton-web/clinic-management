<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()->load('roles:id,name')]);
    }

    public function update(UpdateProfileRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $user = $request->user();
        $original = $user->getOriginal();

        $user->fill($request->safe()->only(['name', 'email', 'phone']));

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        $auditLogger->logChanges($user, $original, "{$user->name} updated their profile");

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }
}
