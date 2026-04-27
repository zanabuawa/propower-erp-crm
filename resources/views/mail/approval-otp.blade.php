@extends('layouts.mail')

@section('content')
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="center">
<table border="0" cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;">
<tr>
  <td style="background-color:#ffffff;border-radius:12px;border:1px solid #e2e8f0;padding:40px 48px;font-family:Arial,sans-serif;">

    {{-- Badge --}}
    <p style="margin:0 0 20px;text-align:center;">
      <span style="background-color:#fee2e2;color:#991b1b;padding:4px 16px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Verificación de Seguridad</span>
    </p>

    {{-- Título --}}
    <h2 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#0f172a;font-family:Arial,sans-serif;line-height:1.2;">
      Código de Aprobación
    </h2>

    {{-- Divider --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr><td style="border-top:1px solid #f1f5f9;padding-top:24px;"></td></tr>
    </table>

    {{-- Saludo --}}
    <p style="margin:0 0 6px;font-size:15px;color:#334155;line-height:1.6;font-family:Arial,sans-serif;">
      Hola, <strong>{{ $user->name }}</strong>
    </p>
    <p style="margin:0 0 32px;font-size:14px;color:#64748b;line-height:1.6;font-family:Arial,sans-serif;">
      Recibiste este código para aprobar: <strong style="color:#334155;">{{ $context }}</strong>
    </p>

    {{-- Código OTP --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr>
        <td align="center" style="background-color:#fff1f2;border:2px dashed #E21F26;border-radius:10px;padding:24px 16px;">
          <p style="margin:0 0 8px;font-size:11px;font-weight:800;color:#E21F26;text-transform:uppercase;letter-spacing:0.15em;font-family:Arial,sans-serif;">
            Tu código OTP
          </p>
          <p style="margin:0;font-size:44px;font-weight:900;letter-spacing:10px;color:#0f172a;font-family:'Courier New',Courier,monospace;line-height:1.1;">
            {{ $code }}
          </p>
        </td>
      </tr>
    </table>

    {{-- Aviso --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
      <tr>
        <td style="background-color:#fffbeb;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:0 4px 4px 0;">
          <p style="margin:0;font-size:13px;color:#92400e;font-family:Arial,sans-serif;line-height:1.5;">
            <strong>Importante:</strong> Este código expira en <strong>10 minutos</strong> y solo puede usarse una vez.
          </p>
        </td>
      </tr>
    </table>

    {{-- Descargo --}}
    <p style="margin:0;font-size:12px;color:#94a3b8;font-family:Arial,sans-serif;line-height:1.6;text-align:center;">
      Si no solicitaste este código, ignora este correo.
    </p>

  </td>
</tr>
</table>
</td></tr>
</table>
@endsection
