<?php

namespace App\Livewire\Landing;

use App\Models\LandingSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class LandingEditor extends Component
{
    use WithFileUploads;

    public string $tab = 'hero';
    public string $galeriaFilter   = 'Industria';
    public string $serviciosFilter = 'Todos';

    public array $hero      = [];
    public array $oferta    = [];
    public array $nosotros  = [];
    public array $servicios = [];
    public array $galeria   = [];
    public array $contacto  = [];
    public array $footer    = [];

    public $uploadFile = null;
    public string $uploadTarget = '';

    // ── Media picker (galería del landing elige de biblioteca) ────────────────
    public bool   $showPicker   = false;
    public string $pickerFor    = '';   // dot-notation: "galeria.projects.3.img"
    public string $pickerSearch = '';

    #[Computed]
    public function library(): array
    {
        $dir = public_path('assets/img/gallery');
        if (!is_dir($dir)) return [];
        $files = glob($dir . '/*.{jpg,jpeg,png,webp,gif,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
        return array_map(fn($f) => '/assets/img/gallery/' . basename($f), $files);
    }

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
        $parts   = explode('.', $this->pickerFor);
        $section = array_shift($parts);
        $data    = $this->$section;
        data_set($data, implode('.', $parts), $path);
        $this->$section = $data;
        $this->showPicker = false;
        $this->pickerFor  = '';
    }

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyRole(['admin', 'gerente']), 403);

        $this->hero      = LandingSetting::getSection('hero');
        $this->oferta    = LandingSetting::getSection('oferta');
        $this->nosotros  = LandingSetting::getSection('nosotros');
        $this->servicios = LandingSetting::getSection('servicios');
        $this->galeria = LandingSetting::getSection('galeria');
        // Migrate old projects structure to simplified items
        if (!isset($this->galeria['items'])) {
            if (!empty($this->galeria['projects'])) {
                $this->galeria['items'] = array_values(array_map(fn($p) => [
                    'img'    => $p['img'] ?? '',
                    'sector' => $p['sector'] ?? 'Industria',
                ], $this->galeria['projects']));
                unset($this->galeria['projects'], $this->galeria['filters']);
            } else {
                $this->galeria['items'] = [];
            }
        }
        $this->contacto  = LandingSetting::getSection('contacto');
        $this->footer    = LandingSetting::getSection('footer');
    }

    public function save(string $section): void
    {
        LandingSetting::setSection($section, $this->$section);
        $this->dispatch('notify', type: 'success', message: 'Sección guardada correctamente');
    }

    // Hero images
    public function addHeroImage(): void
    {
        $this->hero['images'][] = '';
    }

    public function removeHeroImage(int $i): void
    {
        array_splice($this->hero['images'], $i, 1);
    }

    // Hero stats
    public function addStat(): void
    {
        $this->hero['stats'][] = ['value' => '', 'label' => ''];
    }

    public function removeStat(int $i): void
    {
        array_splice($this->hero['stats'], $i, 1);
    }

    // Oferta sectors
    public function addSector(): void
    {
        $this->oferta['sectors'][] = ['image' => '', 'title' => '', 'desc' => '', 'tags' => []];
    }

    public function removeSector(int $i): void
    {
        array_splice($this->oferta['sectors'], $i, 1);
    }

    public function updateSectorTags(int $i, string $raw): void
    {
        $this->oferta['sectors'][$i]['tags'] = array_values(
            array_filter(array_map('trim', explode(',', $raw)))
        );
    }

    // Services
    public function addService(string $cat): void
    {
        $this->servicios[$cat][] = ['img' => '', 't' => '', 'on_landing' => true];
    }

    public function removeService(string $cat, int $i): void
    {
        array_splice($this->servicios[$cat], $i, 1);
    }

    public function toggleServiceOnLanding(string $cat, int $i): void
    {
        $current = $this->servicios[$cat][$i]['on_landing'] ?? true;
        $this->servicios[$cat][$i]['on_landing'] = !$current;
    }

    // Gallery items (simplified: img + sector only)
    public function addItemAndPick(string $sector): void
    {
        $this->galeria['items'][] = ['img' => '', 'sector' => $sector];
        $newIdx = count($this->galeria['items']) - 1;
        $this->openPicker("galeria.items.{$newIdx}.img");
    }

    public function removeItem(int $i): void
    {
        array_splice($this->galeria['items'], $i, 1);
        $this->galeria['items'] = array_values($this->galeria['items']);
    }

    // Sucursales
    public function addSucursal(): void
    {
        $this->contacto['sucursales'][] = ['title' => '', 'embed' => ''];
    }

    public function removeSucursal(int $i): void
    {
        array_splice($this->contacto['sucursales'], $i, 1);
    }

    public function setUploadTarget(string $target): void
    {
        $this->uploadTarget = $target;
        $this->uploadFile   = null;
    }

    public function updatedUploadFile(): void
    {
        if (!$this->uploadFile || !$this->uploadTarget) return;

        $this->validate(['uploadFile' => 'image|mimes:jpeg,jpg,png,webp,gif|max:8192']);

        $dir = public_path('assets/img/landing');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $ext      = strtolower($this->uploadFile->getClientOriginalExtension()) ?: 'jpg';
        $filename = 'img_' . uniqid() . '.' . $ext;
        $this->uploadFile->move($dir, $filename);

        // Apply to the correct nested field via dot-notation
        $parts   = explode('.', $this->uploadTarget);
        $section = array_shift($parts);
        $nested  = implode('.', $parts);
        $data    = $this->$section;
        data_set($data, $nested, '/assets/img/landing/' . $filename);
        $this->$section = $data;

        $this->uploadFile   = null;
        $this->uploadTarget = '';

        $this->dispatch('notify', type: 'success', message: 'Imagen subida correctamente');
    }

    public function render()
    {
        return view('livewire.landing.landing-editor');
    }
}
