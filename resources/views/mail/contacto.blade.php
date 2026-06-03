@extends('layouts.mail')

@section('content')
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr><td align="center">
<table border="0" cellpadding="0" cellspacing="0" width="580" style="width:580px;max-width:580px;">
<tr>
  <td style="background-color:#ffffff;border-radius:12px;border:1px solid #e2e8f0;padding:40px 48px;font-family:Arial,sans-serif;">

    {{-- Badge --}}
    <p style="margin:0 0 20px;text-align:center;">
      <span style="background-color:#fee2e2;color:#991b1b;padding:4px 16px;border-radius:20px;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Nuevo Contacto Web</span>
    </p>

    {{-- Título --}}
    <h2 style="margin:0 0 8px;text-align:center;font-size:24px;font-weight:800;color:#0f172a;font-family:Arial,sans-serif;line-height:1.2;">
      Solicitud de Contacto
    </h2>

    {{-- Divider --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:28px;">
      <tr><td style="border-top:1px solid #f1f5f9;padding-top:24px;"></td></tr>
    </table>

    {{-- Datos del solicitante --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
      <tr>
        <td style="background-color:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;padding:24px 28px;">
          <p style="margin:0 0 16px;font-size:11px;font-weight:800;color:#E21F26;text-transform:uppercase;letter-spacing:0.15em;font-family:Arial,sans-serif;">
            Datos del solicitante
          </p>
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td style="padding:7px 0;border-bottom:1px solid #f1f5f9;vertical-align:top;width:90px;">
                <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Nombre</span>
              </td>
              <td style="padding:7px 0 7px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                <span style="font-size:14px;color:#1e293b;font-weight:600;font-family:Arial,sans-serif;">{{ $data['nombre'] }}</span>
              </td>
            </tr>
            @if(!empty($data['empresa']))
            <tr>
              <td style="padding:7px 0;border-bottom:1px solid #f1f5f9;vertical-align:top;width:90px;">
                <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Empresa</span>
              </td>
              <td style="padding:7px 0 7px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                <span style="font-size:14px;color:#1e293b;font-weight:600;font-family:Arial,sans-serif;">{{ $data['empresa'] }}</span>
              </td>
            </tr>
            @endif
            <tr>
              <td style="padding:7px 0;border-bottom:1px solid #f1f5f9;vertical-align:top;width:90px;">
                <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Correo</span>
              </td>
              <td style="padding:7px 0 7px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                <a href="mailto:{{ $data['correo'] }}" style="font-size:14px;color:#E21F26;font-weight:600;font-family:Arial,sans-serif;text-decoration:none;">{{ $data['correo'] }}</a>
              </td>
            </tr>
            @if(!empty($data['telefono']))
            <tr>
              <td style="padding:7px 0;border-bottom:1px solid #f1f5f9;vertical-align:top;width:90px;">
                <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Teléfono</span>
              </td>
              <td style="padding:7px 0 7px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top;">
                <span style="font-size:14px;color:#1e293b;font-weight:600;font-family:Arial,sans-serif;">{{ $data['telefono'] }}</span>
              </td>
            </tr>
            @endif
            @if(!empty($data['sector']))
            <tr>
              <td style="padding:7px 0;vertical-align:top;width:90px;">
                <span style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;font-family:Arial,sans-serif;">Sector</span>
              </td>
              <td style="padding:7px 0 7px 14px;vertical-align:top;">
                <span style="font-size:14px;color:#1e293b;font-weight:600;font-family:Arial,sans-serif;">{{ $data['sector'] }}</span>
              </td>
            </tr>
            @endif
          </table>
        </td>
      </tr>
    </table>

    {{-- Bloque de mensaje --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
      <tr>
        <td style="background-color:#1e293b;border-radius:10px;padding:24px 28px;">
          <p style="margin:0 0 12px;font-size:11px;font-weight:800;color:#E21F26;text-transform:uppercase;letter-spacing:0.15em;font-family:Arial,sans-serif;">
            Mensaje
          </p>
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td style="border-left:4px solid #E21F26;padding:16px 20px;background-color:#0f172a;border-radius:0 6px 6px 0;">
                <p style="margin:0;font-style:italic;font-size:15px;color:#e2e8f0;font-weight:500;line-height:1.7;font-family:Arial,sans-serif;">
                  "{{ $data['mensaje'] }}"
                </p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    {{-- Estatus / recibido --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:24px;">
      <tr>
        <td style="background-color:#0f172a;border-radius:10px;padding:24px 28px;">
          <table border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
              <td style="vertical-align:middle;">
                <p style="margin:0 0 4px;font-size:11px;font-weight:800;color:#E21F26;text-transform:uppercase;letter-spacing:0.15em;font-family:Arial,sans-serif;">Estatus</p>
                <p style="margin:0;font-size:22px;font-weight:900;color:#ffffff;font-family:Arial,sans-serif;">RECIBIDO</p>
              </td>
              <td align="right" style="vertical-align:middle;">
                <p style="margin:0 0 4px;font-size:11px;color:#64748b;font-family:Arial,sans-serif;">Fecha y hora</p>
                <p style="margin:0;font-size:14px;font-weight:700;color:#ffffff;font-family:Arial,sans-serif;">{{ now()->format('d/m/Y H:i') }} hrs</p>
              </td>
            </tr>
          </table>
        </td>
      </tr>
    </table>

    {{-- Nota de respuesta --}}
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom:8px;">
      <tr>
        <td style="background-color:#fffbeb;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:0 4px 4px 0;">
          <p style="margin:0;font-size:13px;color:#92400e;font-family:Arial,sans-serif;line-height:1.5;">
            Responde directamente a este correo para contactar a <strong>{{ $data['nombre'] }}</strong> en <strong>{{ $data['correo'] }}</strong>.
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
