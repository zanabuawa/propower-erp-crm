<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de verificación</title>
    <style>
        body { margin: 0; padding: 0; background: #f8fafc; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 520px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header  { background: #4f46e5; padding: 28px 32px; }
        .header h1 { margin: 0; color: #fff; font-size: 18px; font-weight: 600; }
        .body    { padding: 32px; }
        .greeting { font-size: 15px; color: #1e293b; margin: 0 0 16px; }
        .ctx     { font-size: 14px; color: #475569; margin: 0 0 28px; }
        .code-box { background: #f1f5f9; border: 2px dashed #c7d2fe; border-radius: 10px; text-align: center; padding: 20px 0; margin-bottom: 28px; }
        .code    { font-size: 42px; font-weight: 700; letter-spacing: 10px; color: #4f46e5; font-family: 'Courier New', monospace; }
        .expiry  { font-size: 13px; color: #64748b; margin: 0 0 24px; }
        .warning { font-size: 12px; color: #94a3b8; border-top: 1px solid #f1f5f9; padding-top: 20px; margin-top: 8px; }
        .footer  { background: #f8fafc; padding: 16px 32px; text-align: center; }
        .footer p { margin: 0; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
        </div>
        <div class="body">
            <p class="greeting">Hola, <strong>{{ $user->name }}</strong></p>
            <p class="ctx">
                Recibiste este correo porque se solicitó un código de verificación para:
                <strong>{{ $context }}</strong>.
            </p>
            <div class="code-box">
                <div class="code">{{ $code }}</div>
            </div>
            <p class="expiry">
                Este código es válido por <strong>10 minutos</strong> y solo puede usarse una vez.
            </p>
            <p class="warning">
                Si no solicitaste este código, ignora este correo. Nadie más puede usar este código sin acceso a tu cuenta.
            </p>
        </div>
        <div class="footer">
            <p>{{ config('app.name') }} &middot; Sistema ERP</p>
        </div>
    </div>
</body>
</html>
