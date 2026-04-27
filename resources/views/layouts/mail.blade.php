<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, table, td, p, a, li, blockquote { -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; background-color: #f1f5f9; }

        .full-row { width: 100% !important; }
        .container-wide { width: 100%; max-width: 1000px; margin: 0 auto; }

        .header-band { background-color: #ffffff; border-top: 6px solid #E21F26; }

        /* Kept for non-Gmail clients as enhancement */
        .btn-pro { box-shadow: 0 10px 15px -3px rgba(226, 31, 38, 0.3); }

        @media screen and (max-width: 900px) {
            .container-wide { width: 100% !important; }
            .column-main { padding-right: 0 !important; display: block !important; width: 100% !important; }
            .column-side { display: block !important; width: 100% !important; margin-top: 30px; }
            .mobile-padding { padding: 20px !important; }
        }
    </style>
</head>
<body style="background-color: #f1f5f9; margin: 0; padding: 0;">

    <!-- HEADER -->
    <table border="0" cellpadding="0" cellspacing="0" class="full-row header-band" style="width: 100%; background-color: #ffffff; border-top: 6px solid #E21F26;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" class="container-wide" style="width: 100%; max-width: 1000px;">
                    <tr>
                        <td style="padding: 25px 20px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td align="left">
                                        @php
                                            $logoUrl = isset($company) && $company?->print_logo
                                                ? asset('storage/' . $company->print_logo)
                                                : (isset($company) && $company?->logo ? asset('storage/' . $company->logo) : null);
                                        @endphp
                                        @if($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $company?->name ?? 'ProPower' }}" width="200" style="display: block; border: 0;">
                                        @else
                                            <h1 style="margin: 0; color: #0f172a; font-family: Arial, sans-serif; font-size: 24px; font-weight: 900; letter-spacing: -0.5px;">PRO<span style="color: #E21F26;">POWER</span></h1>
                                        @endif
                                    </td>
                                    <td align="right" style="font-family: Arial, sans-serif; font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 2px;">
                                        Enterprise Resource Planning
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- MAIN CONTENT -->
    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; padding: 60px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" class="container-wide" style="width: 100%; max-width: 1000px;">
                    <tr>
                        <td class="mobile-padding" style="padding: 0 20px;">
                            @yield('content')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <table border="0" cellpadding="0" cellspacing="0" style="width: 100%; background-color: #0f172a; padding: 50px 0;">
        <tr>
            <td align="center">
                <table border="0" cellpadding="0" cellspacing="0" class="container-wide" style="width: 100%; max-width: 1000px;">
                    <tr>
                        <td class="mobile-padding" style="padding: 0 20px;">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td style="vertical-align: top;">
                                        <p style="margin: 0; font-size: 16px; font-weight: bold; color: #ffffff; font-family: Arial, sans-serif;">{{ $company?->name ?? 'ProPower Electroconstrucciones' }}</p>
                                        <p style="margin: 10px 0 0 0; font-size: 13px; line-height: 1.6; color: #94a3b8; font-family: Arial, sans-serif;">
                                            {{ $company?->address ?? 'Chihuahua, México' }}<br>
                                            <a href="mailto:{{ $company?->email ?? 'contacto@propower.mx' }}" style="color: #E21F26; text-decoration: none;">{{ $company?->email ?? 'contacto@propower.mx' }}</a>
                                        </p>
                                    </td>
                                    <td align="right" style="vertical-align: bottom;">
                                        <p style="margin: 0; font-size: 11px; color: #94a3b8; font-family: Arial, sans-serif;">&copy; {{ date('Y') }} ProPower ERP. Todos los derechos reservados.</p>
                                        <p style="margin: 5px 0 0 0; font-size: 11px; font-style: italic; color: #64748b; font-family: Arial, sans-serif;">"Calidad y atención personalizada desde el primer contacto"</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>
