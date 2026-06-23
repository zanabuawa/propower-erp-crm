<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\HrEmployee;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, TwoFactorAuthenticator $authenticator): View
    {
        $user = $request->user();
        $employee = HrEmployee::query()
            ->with(['branch', 'department', 'position', 'supervisor'])
            ->where('user_id', $user->id)
            ->first();

        return view('profile.edit', [
            'user' => $user,
            'employee' => $employee,
            'twoFactorProvisioningUri' => $user->two_factor_pending_secret
                ? $authenticator->provisioningUri($user, $user->two_factor_pending_secret)
                : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $fullName = trim(implode(' ', array_filter([
            $data['first_name'],
            $data['last_name'],
            $data['second_last_name'] ?? null,
        ])));

        $user = $request->user();
        $employee = HrEmployee::query()
            ->where('user_id', $user->id)
            ->first();

        if ($employee) {
            $employee->forceFill([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'second_last_name' => $data['second_last_name'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'gender' => $data['gender'] ?? null,
            ])->save();
        }

        $user->forceFill([
            'name' => $fullName,
            'birth_date' => $data['birth_date'] ?? null,
            'gender' => $data['gender'] ?? null,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateSignature(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('signature', [
            'signature' => ['required', 'string', 'regex:/^data:image\/png;base64,[A-Za-z0-9+\/=]+$/', 'max:2000000'],
        ]);

        $request->user()->forceFill([
            'signature' => $validated['signature'],
            'signature_updated_at' => now(),
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'signature-updated');
    }

    public function destroySignature(Request $request): RedirectResponse
    {
        $request->user()->forceFill([
            'signature' => null,
            'signature_updated_at' => null,
        ])->save();

        return Redirect::route('profile.edit')->with('status', 'signature-deleted');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        return Redirect::route('profile.edit')->with('status', 'account-delete-disabled');
    }
}
