<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApprovalOtpCode extends Model
{
    protected $fillable = [
        'user_id',
        'purpose',
        'reference_id',
        'code_hash',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Factory helpers ──────────────────────────────────────────────────────

    /**
     * Genera y persiste un nuevo OTP de 6 dígitos para el usuario dado.
     * Invalida cualquier OTP previo no usado del mismo propósito + referencia.
     *
     * @return string  El código en texto plano (enviar al usuario; NO guardar)
     */
    public static function generate(User $user, string $purpose, ?int $referenceId = null): string
    {
        // Invalidar OTPs anteriores del mismo contexto
        static::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('reference_id', $referenceId)
            ->whereNull('used_at')
            ->delete();

        $plainCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        static::create([
            'user_id'      => $user->id,
            'purpose'      => $purpose,
            'reference_id' => $referenceId,
            'code_hash'    => hash('sha256', $plainCode),
            'expires_at'   => now()->addMinutes(10),
        ]);

        return $plainCode;
    }

    /**
     * Verifica si el código en texto plano es válido (no expirado, no usado).
     * Si es válido lo marca como usado y retorna true.
     */
    public static function verify(User $user, string $purpose, ?int $referenceId, string $plainCode): bool
    {
        $otp = static::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('reference_id', $referenceId)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otp) return false;

        if (!hash_equals($otp->code_hash, hash('sha256', $plainCode))) {
            return false;
        }

        $otp->update(['used_at' => now()]);
        return true;
    }
}
