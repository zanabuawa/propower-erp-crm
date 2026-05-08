<?php

namespace App\Livewire\Landing;

use App\Models\LandingSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class GalleryEditor extends Component
{
    use WithFileUploads;

    public string $tab          = '0';
    public array  $categories   = [];

    // ── Picker ────────────────────────────────────────────────────────────────
    public bool   $showPicker   = false;
    public string $pickerFor    = '';   // "catIdx.imgIdx"
    public string $pickerSearch = '';

    // ── Uploads ───────────────────────────────────────────────────────────────
    public $libraryUpload = null;   // upload directo a la biblioteca

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'gerente']), 403);
        $this->loadCategories();
    }

    // ── Computed: escanea gallery/ (uploads) + Galeria/** (existentes) ──────────
    #[Computed]
    public function library(): array
    {
        $ext  = '*.{jpg,jpeg,png,webp,gif,JPG,JPEG,PNG,WEBP}';
        $files = [];

        // Uploads nuevos (plano)
        $flat = public_path('assets/img/gallery');
        if (is_dir($flat)) {
            $files = array_merge($files, glob($flat . '/' . $ext, GLOB_BRACE) ?: []);
        }

        // Imágenes existentes en subcarpetas de Galeria/
        $galeria = public_path('assets/img/Galeria');
        if (is_dir($galeria)) {
            foreach (glob($galeria . '/*', GLOB_ONLYDIR) ?: [] as $sub) {
                $files = array_merge($files, glob($sub . '/' . $ext, GLOB_BRACE) ?: []);
            }
        }

        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        return array_map(function ($f) {
            $rel = str_replace(public_path(), '', $f);
            return '/' . ltrim(str_replace('\\', '/', $rel), '/');
        }, $files);
    }

    // ── Picker ────────────────────────────────────────────────────────────────
    public function openPicker(string $target): void
    {
        $this->pickerFor    = $target;
        $this->pickerSearch = '';
        $this->showPicker   = true;
    }

    public function closePicker(): void
    {
        $this->showPicker = false;
        $this->pickerFor  = '';
    }

    public function selectMedia(string $path): void
    {
        if (!$this->pickerFor) return;
        [$catIdx, $imgIdx] = explode('.', $this->pickerFor);
        $this->categories[(int)$catIdx]['images'][(int)$imgIdx] = $path;
        $this->showPicker = false;
        $this->pickerFor  = '';
    }

    // ── Upload a la biblioteca ────────────────────────────────────────────────
    public function updatedLibraryUpload(): void
    {
        if (!$this->libraryUpload) return;
        $this->validate(['libraryUpload' => 'image|mimes:jpeg,jpg,png,webp,gif|max:8192']);

        $dir = public_path('assets/img/gallery');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $ext      = strtolower($this->libraryUpload->getClientOriginalExtension()) ?: 'jpg';
        $filename = 'gal_' . uniqid() . '.' . $ext;
        $this->libraryUpload->move($dir, $filename);
        $this->libraryUpload = null;

        unset($this->library); // invalida computed
        $this->dispatch('notify', type: 'success', message: 'Foto subida a la biblioteca');
    }

    // ── Guardado ──────────────────────────────────────────────────────────────
    public function saveCategory(int $i): void
    {
        LandingSetting::setSection('gallery_page', ['categories' => $this->categories]);
        $this->dispatch('notify', type: 'success', message: 'Categoría guardada');
    }

    public function saveAll(): void
    {
        LandingSetting::setSection('gallery_page', ['categories' => $this->categories]);
        $this->dispatch('notify', type: 'success', message: 'Galería guardada correctamente');
    }

    // ── Categorías ────────────────────────────────────────────────────────────
    public function addCategory(): void
    {
        $this->categories[] = [
            'id' => 'categoria-' . uniqid(), 'title' => '', 'short' => '',
            'sector' => 'Industria', 'desc' => '', 'images' => [],
        ];
        $this->tab = (string)(count($this->categories) - 1);
    }

    public function removeCategory(int $i): void
    {
        array_splice($this->categories, $i, 1);
        $this->categories = array_values($this->categories);
        $this->tab = '0';
        LandingSetting::setSection('gallery_page', ['categories' => $this->categories]);
        $this->dispatch('notify', type: 'success', message: 'Categoría eliminada');
    }

    // ── Imágenes ──────────────────────────────────────────────────────────────
    public function addImage(int $catIdx): void
    {
        $this->categories[$catIdx]['images'][] = '';
    }

    public function removeImage(int $catIdx, int $imgIdx): void
    {
        array_splice($this->categories[$catIdx]['images'], $imgIdx, 1);
        $this->categories[$catIdx]['images'] = array_values($this->categories[$catIdx]['images']);
    }

    // ── Render ────────────────────────────────────────────────────────────────
    public function render()
    {
        return view('livewire.landing.gallery-editor');
    }

    // ── Carga de categorías ───────────────────────────────────────────────────
    private function loadCategories(): void
    {
        $stored = LandingSetting::getSection('gallery_page');
        $this->categories = !empty($stored['categories'])
            ? $stored['categories']
            : $this->staticDefaults();
    }

    private function staticDefaults(): array
    {
        return [
            ['id'=>'baja-tension',      'title'=>'Baja Tensión',                     'short'=>'Baja Tensión',  'sector'=>'Industria',    'desc'=>'Instalaciones eléctricas en baja tensión para naves industriales y comerciales.',     'images'=>array_map(fn($n)=>"/assets/img/Galeria/Baja-Tension/{$n}.webp",         range(1,42))],
            ['id'=>'media-tension',     'title'=>'Media Tensión',                    'short'=>'Media Tensión', 'sector'=>'Industria',    'desc'=>'Subestaciones, líneas y acometidas de media tensión.',                              'images'=>array_map(fn($n)=>"/assets/img/Galeria/Media-Tension/{$n}.webp",         range(1,54))],
            ['id'=>'pruebas-electricas','title'=>'Pruebas Eléctricas',               'short'=>'Pruebas',       'sector'=>'Mantenimiento','desc'=>'Pruebas a equipo eléctrico: aislamiento, rigidez, continuidad y puesta a tierra.','images'=>array_map(fn($n)=>"/assets/img/Galeria/Pruebas Electricas/{$n}.webp",       range(1,5))],
            ['id'=>'habilitacion',      'title'=>'Habilitación Eléctrica',           'short'=>'Habilitación',  'sector'=>'Industria',    'desc'=>'Habilitación de instalaciones eléctricas para nuevos espacios industriales.',       'images'=>array_map(fn($n)=>"/assets/img/Galeria/Habilitacion electrica/{$n}.webp",  range(1,4))],
            ['id'=>'tableros',          'title'=>'Mejora de Tableros Eléctricos',    'short'=>'Tableros',      'sector'=>'Mantenimiento','desc'=>'Modernización y mejora de tableros existentes para cumplir con norma actual.',   'images'=>array_map(fn($n)=>"/assets/img/Galeria/Mejora de tableros electricos existentes/{$n}.webp",range(1,7))],
            ['id'=>'control',           'title'=>'Actualizaciones de Control',       'short'=>'Control',       'sector'=>'Ingeniería',   'desc'=>'Actualizaciones de sistemas de control para plantas energéticas.',                 'images'=>array_map(fn($n)=>"/assets/img/Galeria/Actualizaciones de control para planta energetica/{$n}.webp",range(1,6))],
            ['id'=>'rodillo',           'title'=>'Reparación de Rodillo de Roladora','short'=>'Roladora',      'sector'=>'Industria',    'desc'=>'Desmontaje y reparación de rodillo de roladora industrial.',                       'images'=>array_map(fn($n)=>"/assets/img/Galeria/Desmontaje y reparacionde de rodillo de roladora/{$n}.webp",range(1,5))],
            ['id'=>'molino',            'title'=>'Laminado de Estructura de Molino', 'short'=>'Molino',        'sector'=>'Industria',    'desc'=>'Trabajos de laminado en estructura de molino industrial.',                         'images'=>array_map(fn($n)=>"/assets/img/Galeria/Laminado de estructura de molino/{$n}.webp",range(1,4))],
            ['id'=>'iluminacion',       'title'=>'Iluminación LED y Láminas',        'short'=>'Iluminación',   'sector'=>'Mantenimiento','desc'=>'Reemplazo de láminas translúcidas y actualización de iluminación LED.',          'images'=>array_map(fn($n)=>"/assets/img/Galeria/Remplazo de laminas translucidas y actualizacion de iluminacion led/{$n}.webp",range(1,19))],
            ['id'=>'venta',             'title'=>'Venta de Equipo y Material',       'short'=>'Equipo',        'sector'=>'Comercial',    'desc'=>'Venta y distribución de equipo y material eléctrico industrial.',                  'images'=>array_map(fn($n)=>"/assets/img/Galeria/Venta de equipo y material electrico/{$n}.webp",range(1,16))],
        ];
    }
}
