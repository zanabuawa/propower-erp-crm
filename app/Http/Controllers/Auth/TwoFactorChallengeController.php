<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorAuthenticator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('login.two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request, TwoFactorAuthenticator $authenticator): RedirectResponse
    {
        $request->validate([
            'code' => ['nullable', 'string'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $userId = $request->session()->get('login.two_factor_user_id');
        $remember = (bool) $request->session()->get('login.remember', false);
        $user = User::findOrFail($userId);
        $key = 'two-factor-challenge:'.$user->id.'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'code' => 'Demasiados intentos. Espera '.RateLimiter::availableIn($key).' segundos.',
            ]);
        }

        $valid = false;

        if ($request->filled('code')) {
            $valid = $authenticator->verify($user->two_factor_secret, $request->string('code')->toString());
        }

        if (! $valid && $request->filled('recovery_code')) {
            $valid = $this->consumeRecoveryCode($user, $request->string('recovery_code')->toString());
        }

        if (! $valid) {
            RateLimiter::hit($key);

            throw ValidationException::withMessages([
                'code' => 'El codigo de verificacion no es valido.',
            ]);
        }

        RateLimiter::clear($key);
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->forget(['login.two_factor_user_id', 'login.remember']);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $normalized = strtoupper(trim($code));

        foreach ($codes as $index => $hashedCode) {
            if (Hash::check($normalized, $hashedCode)) {
                unset($codes[$index]);
                $user->forceFill([
                    'two_factor_recovery_codes' => array_values($codes),
                ])->save();

                return true;
            }
        }

        return false;
    }
}
