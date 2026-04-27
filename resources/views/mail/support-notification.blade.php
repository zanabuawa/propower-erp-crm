@extends('layouts.mail')

@section('content')
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="center">
<table border="0" cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;">
<tr>
  <td style="background-color:#ffffff;border-radius:12px;border:1px solid #e2e8f0;padding:40px 48px;font-family:Arial,sans-serif;">

    {{-- Badge --}}
    <p style="margin:0 0 20px;text-align:center;">
      <span style="background-color:#dcfce7;color:#166534;padding:4px 16px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Solicitud Recibida</span>
    </p>

    {{-- Título --}}
    <h2 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#0f172a;font-family:Arial,sans-serif;line-height:1.2;">
      Solicitud Confirmada
    </h2>

    {{-- Divider --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr><td style="border-top:1px solid #f1f5f9;padding-top:24px;"></td></tr>
    </table>

    {{-- Intro --}}
    <p style="margin:0 0 32px;font-size:14px;color:#64748b;line-height:1.6;font-family:Arial,sans-serif;">
      Hemos recibido su ticket de soporte. Un especialista de <strong style="color:#334155;">{{ $company?->name ?? 'ProPower' }}</strong> ha sido asignado para darle seguimiento inmediato.
    </p>

    {{-- Detalle del mensaje --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
      <tr>
        <td style="background-color:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;padding:24px 28px;">
          <p style="margin:0 0 12px;font-size:11px;font-weight:800;color:#E21F26;text-transform:uppercase;letter-spacing:0.15em;font-family:Arial,sans-serif;">
            Detalle de su consulta
          </p>
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td style="border-left:4px solid #1e293b;padding:16px 20px;background-color:#ffffff;border-radius:0 6px 6px 0;">
                <p style="margin:0;font-style:italic;font-size:15px;color:#1e293b;font-weight:500;line-height:1.6;font-family:Arial,sans-serif;">
                  "{{ $userMessage }}"
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    {{-- Estatus --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr>
        <td style="background-color:#1e293b;border-radius:10px;padding:24px 28px;">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td style="vertical-align:middle;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:800;color:#E21F26;text-transform:uppercase;letter-spacing:0.15em;font-family:Arial,sans-serif;">Estatus</p>
                <p style="margin:0;font-size:22px;font-weight:900;color:#ffffff;font-family:Arial,sans-serif;">RECIBIDO</p>
              </td>
              <td align="right" style="vertical-align:middle;">
                <p style="margin:0 0 4px;font-size:11px;color:#64748b;font-family:Arial,sans-serif;">Folio</p>
                <p style="margin:0;font-size:16px;font-weight:700;color:#ffffff;font-family:Arial,sans-serif;">{{ $folio ?? '#' . date('Ymd') . '-001' }}</p>
              </td>
            </tr>
          </table>
          <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top:18px;border-top:1px solid #334155;">
            <tr>
              <td style="padding-top:16px;">
                <p style="margin:0 0 4px;font-size:11px;color:#64748b;font-family:Arial,sans-serif;">Próximo paso</p>
                <p style="margin:0;font-size:14px;font-weight:600;color:#ffffff;font-family:Arial,sans-serif;">Asignación de Ingeniero</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    {{-- Nota de ayuda --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr>
        <td style="background-color:#fffbeb;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:0 4px 4px 0;">
          <p style="margin:0;font-size:13px;color:#92400e;font-family:Arial,sans-serif;line-height:1.5;">
            ¿Tiene más información? Responda a este correo adjuntando capturas de pantalla si es necesario.
          </p>
        </td>
      </tr>
    </table>

    {{-- CTA --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td align="center">
          <a href="{{ config('app.url') }}/dashboard"
             style="background-color:#E21F26;color:#ffffff;padding:14px 36px;text-decoration:none;border-radius:8px;font-weight:700;display:inline-block;font-size:14px;text-transform:uppercase;font-family:Arial,sans-serif;letter-spacing:0.05em;">
            Acceder a mi Dashboard
          </a>
        </td>
      </tr>
    </table>

  </td>
</tr>
</table>
</td></tr>
</table>
@endsection
