@php
    try {
        $landingData = \App\Models\LandingSetting::allSections();
    } catch (\Exception $e) {
        $landingData = [];
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ProPower Electroconstrucciones — Soluciones, calidad y garantía</title>
  <meta name="description" content="Empresa 100% mexicana especializada en servicios electromecánicos industriales y comerciales. Industria, minería e ingeniería en Chihuahua." />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <script>window.__LANDING_DATA__ = {!! json_encode($landingData) !!};</script>
  <style>
    html, body { margin: 0; padding: 0; background: #0a0a0a; }
    .pp-desktop { display: block; }
    .pp-mobile  { display: none; }
    @media (max-width: 720px) {
      .pp-desktop { display: none; }
      .pp-mobile  { display: block; }
    }
  </style>
  @viteReactRefresh
  @vite('resources/landing/index.jsx')
</head>
<body>
  <div id="root"></div>
</body>
</html>
