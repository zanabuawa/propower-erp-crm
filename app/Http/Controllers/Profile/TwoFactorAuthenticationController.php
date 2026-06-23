<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;

class TwoFactorAuthenticationController extends Controller
{
    public function start(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return Redirect::route('profile.edit')->with('two_factor_status', 'two-factor-already-enabled');
        }

        $secret = $authenticator->generateSecret();

        $user->forceFill([
            'two_factor_pending_secret' => $secret,
        ])->save();

        return Redirect::route('profile.edit')->with('two_factor_status', 'two-factor-started');
    }

    public function confirm(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
        ]);

        $user = $request->user();
        $secret = $user->two_factor_pending_secret;

        if (! $secret || ! $authenticator->verify($secret, $request->string('code')->toString())) {
            throw ValidationException::withMessages([
                'code' => 'El codigo no coincide con tu app autenticadora.',
            ]);
        }

        $plainRecoveryCodes = $authenticator->recoveryCodes();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_pending_secret' => null,
            'two_factor_recovery_codes' => collect($plainRecoveryCodes)->map(fn ($code) => Hash::make($code))->all(),
            'two_factor_confirmed_at' => now(),
        ])->save();

        return Redirect::route('profile.edit')
            ->with('two_factor_status', 'two-factor-enabled')
            ->with('two_factor_recovery_codes_plain', $plainRecoveryCodes);
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $request->user()->forceFill([
            'two_factor_secret' => null,
            'two_factor_pending_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return Redirect::route('profile.edit')->with('two_factor_status', 'two-factor-disabled');
    }

    public function regenerateRecoveryCodes(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return Redirect::route('profile.edit')->with('two_factor_status', 'two-factor-not-enabled');
        }

        $plainRecoveryCodes = $authenticator->recoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => collect($plainRecoveryCodes)->map(fn ($code) => Hash::make($code))->all(),
        ])->save();

        return Redirect::route('profile.edit')
            ->with('two_factor_status', 'recovery-codes-regenerated')
            ->with('two_factor_recovery_codes_plain', $plainRecoveryCodes);
    }
}
