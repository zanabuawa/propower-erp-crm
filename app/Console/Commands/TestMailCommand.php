<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    protected $signature = 'mail:test
                            {--to= : Correo destino (por defecto usa MAIL_FROM_ADDRESS)}
                            {--view=all : Vista a enviar: all, otp, reset, support}';

    protected $description = 'Envía correos de prueba para verificar las vistas de correo';

    public function handle(): int
    {
        $to      = $this->option('to') ?? config('mail.from.address');
        $view    = $this->option('view');
        $company = Company::first();
        $user    = User::first();

        if (! $user) {
            $this->error('No hay usuarios en la base de datos.');
            return 1;
        }

        $this->info("Enviando correos de prueba a: {$to}");

        $sent = 0;

        if (in_array($view, ['all', 'otp'])) {
            Mail::send('mail.approval-otp', [
                'user'    => $user,
                'code'    => '482917',
                'context' => 'Ajuste de inventario #INV-2024-001',
                'company' => $company,
            ], fn ($m) => $m->to($to)->subject('[TEST] Código de Aprobación OTP'));
            $this->line('  ✓ OTP enviado');
            $sent++;
        }

        if (in_array($view, ['all', 'reset'])) {
            Mail::send('mail.auth.reset-password', [
                'user'    => $user,
                'url'     => config('app.url') . '/password/reset/token-de-prueba?email=' . urlencode($user->email),
                'company' => $company,
            ], fn ($m) => $m->to($to)->subject('[TEST] Restablecer Contraseña'));
            $this->line('  ✓ Reset password enviado');
            $sent++;
        }

        if (in_array($view, ['all', 'support'])) {
            Mail::send('mail.support-notification', [
                'userMessage' => 'No puedo acceder al módulo de inventario, aparece un error 403 al intentar ver los movimientos de stock.',
                'folio'       => '#' . date('Ymd') . '-001',
                'company'     => $company,
            ], fn ($m) => $m->to($to)->subject('[TEST] Solicitud de Soporte Confirmada'));
            $this->line('  ✓ Support notification enviado');
            $sent++;
        }

        if ($sent === 0) {
            $this->error("Vista '{$view}' no reconocida. Usa: all, otp, reset, support");
            return 1;
        }

        $this->info("Listo. {$sent} correo(s) enviado(s).");
        return 0;
    }
}
