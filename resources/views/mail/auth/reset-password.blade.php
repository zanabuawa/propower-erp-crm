@extends('layouts.mail')

@section('content')
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="center">
<table border="0" cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;">
<tr>
  <td style="background-color:#ffffff;border-radius:12px;border:1px solid #e2e8f0;padding:40px 48px;font-family:Arial,sans-serif;">

    {{-- Badge --}}
    <p style="margin:0 0 20px;text-align:center;">
      <span style="background-color:#fee2e2;color:#991b1b;padding:4px 16px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Seguridad de Cuenta</span>
    </p>

    {{-- Título --}}
    <h2 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#0f172a;font-family:Arial,sans-serif;line-height:1.2;">
      ¿Olvidaste tu contraseña?
    </h2>

    {{-- Divider --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr><td style="border-top:1px solid #f1f5f9;padding-top:24px;"></td></tr>
    </table>

    {{-- Saludo y cuerpo --}}
    <p style="margin:0 0 12px;font-size:15px;color:#334155;line-height:1.6;font-family:Arial,sans-serif;">
      Hola <strong>{{ $user->name }}</strong>,
    </p>
    <p style="margin:0 0 32px;font-size:14px;color:#64748b;line-height:1.6;font-family:Arial,sans-serif;">
      Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong style="color:#334155;">{{ config('app.name') }}</strong>. Haz clic en el botón para continuar.
    </p>

    {{-- Botón CTA --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr>
        <td style="background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:28px;text-align:center;">
          <a href="{{ $url }}"
             style="background-color:#E21F26;color:#ffffff;padding:14px 36px;text-decoration:none;border-radius:8px;font-weight:700;display:inline-block;font-size:14px;text-transform:uppercase;font-family:Arial,sans-serif;letter-spacing:0.05em;">
            Restablecer Contraseña
          </a>
        </td>
      </tr>
    </table>

    {{-- Aviso de expiración --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
      <tr>
        <td style="background-color:#fffbeb;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:0 4px 4px 0;">
          <p style="margin:0;font-size:13px;color:#92400e;font-family:Arial,sans-serif;line-height:1.5;">
            <strong>Importante:</strong> Este enlace expira en <strong>{{ config('auth.passwords.users.expire', 5) }} minutos</strong> por motivos de seguridad.
          </p>
        </td>
      </tr>
    </table>

    {{-- Descargo --}}
    <p style="margin:0 0 24px;font-size:13px;color:#64748b;line-height:1.6;font-family:Arial,sans-serif;">
      Si no solicitaste este cambio, puedes ignorar este correo. Tu contraseña actual no se verá afectada.
    </p>

    {{-- Enlace plano --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td style="border-top:1px dotted #e2e8f0;padding-top:20px;">
          <p style="margin:0;font-size:12px;color:#94a3b8;font-family:Arial,sans-serif;line-height:1.6;">
            Si tienes problemas con el botón, copia y pega este enlace en tu navegador:<br>
            <span style="word-break:break-all;color:#E21F26;">{{ $url }}</span>
          </p>
        </td>
      </tr>
    </table>

  </td>
</tr>
</table>
</td></tr>
</table>
@endsection
