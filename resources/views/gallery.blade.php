<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Galería de Proyectos — ProPower Electroconstrucciones</title>
  <meta name="description" content="Galería de proyectos electromecánicos industriales y comerciales de ProPower Electroconstrucciones en Chihuahua." />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <style>
    html, body { margin: 0; padding: 0; background: #0a0a0a; }
  </style>
  @viteReactRefresh
  @vite('resources/gallery/index.jsx')
  <script>
    window.__GALLERY_DATA__ = @json(\App\Models\LandingSetting::getSection('gallery_page'));
  </script>
</head>
<body>
  <div id="root"></div>
</body>
</html>
