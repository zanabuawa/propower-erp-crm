<?php

namespace App\Livewire\Tenders;

use App\Models\WorkPhotoReport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
#[Title('Formato Fotografico')]
class WorkPhotoReportDocumentEditor extends Component
{
    use WithFileUploads;

    public WorkPhotoReport $report;
    public string $custom_body = '';
    public array $photo_layout = [];
    public array $newPhotos = [];

    public function mount(WorkPhotoReport $report): void
    {
        $report->load(['project.customer', 'project.branch.company', 'createdBy']);

        abort_if(
            ! $report->project->branch || $report->project->branch->company_id !== auth()->user()->company_id,
            404
        );

        $this->report = $report;
        $this->custom_body = $report->custom_body ?: $this->defaultBody();
        $this->photo_layout = $this->normalizePhotoLayout();
    }

    public function save(): void
    {
        $this->validate([
            'custom_body' => 'nullable|string',
        ]);

        $this->report->update([
            'custom_body' => $this->sanitizeHtml($this->custom_body),
        ]);

        session()->flash('success', 'Formato fotografico guardado.');
        $this->skipRender();
    }

    public function resetToDefault(): void
    {
        $this->custom_body = $this->defaultBody();
        $this->dispatch('free-photo-report-reset', body: $this->custom_body);
    }

    public function savePhotoLayout(array $layout): void
    {
        $this->photo_layout = $this->sanitizePhotoLayout($layout);
        $photos = array_column($this->photo_layout, 'path');

        $this->report->update([
            'photos' => $photos,
            'photo_layout' => $this->photo_layout,
        ]);

        $this->report->refresh();
        session()->flash('success', 'Acomodo fotografico guardado.');
    }

    public function saveDocument(string $body, array $layout): void
    {
        $this->custom_body = $this->sanitizeHtml($body);
        $this->photo_layout = $this->sanitizePhotoLayout($layout);
        $photos = array_column($this->photo_layout, 'path');

        $this->report->update([
            'custom_body' => $this->custom_body,
            'photos' => $photos,
            'photo_layout' => $this->photo_layout,
        ]);

        $this->report->refresh();
        session()->flash('success', 'Formato fotografico guardado.');
        $this->skipRender();
    }

    public function deletePhoto(string $path): void
    {
        $photos = collect($this->report->photos ?? [])
            ->reject(fn ($photo) => $photo === $path)
            ->values()
            ->all();

        $layout = collect($this->report->photo_layout ?? [])
            ->filter(fn ($item) => is_array($item) && ($item['path'] ?? null) !== $path)
            ->values()
            ->all();

        if (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
            $storagePath = str_starts_with($path, '/storage/')
                ? substr($path, strlen('/storage/'))
                : ltrim($path, '/');

            Storage::disk('public')->delete($storagePath);
        }

        $this->report->update([
            'photos' => $photos,
            'photo_layout' => $layout,
        ]);

        $this->report->refresh();
        $this->photo_layout = $this->normalizePhotoLayout();

        session()->flash('success', 'Foto eliminada del reporte.');
        $this->skipRender();
    }

    public function addPhotos(): void
    {
        $this->validate([
            'newPhotos' => 'required|array|min:1',
            'newPhotos.*' => 'image|max:5120',
        ]);

        $photos = $this->report->photos ?? [];
        $layout = $this->sanitizePhotoLayout($this->report->photo_layout ?? []);

        foreach ($this->newPhotos as $photo) {
            $path = $photo->store('works/photos', 'public');
            $photos[] = $path;
            $layout[] = [
                'path' => $path,
                'scale' => 50,
                'page' => max(1, collect($layout)->max('page') ?? 1),
                'manual_page' => false,
                'break_after' => false,
                'page_date' => null,
                'hidden' => false,
            ];
        }

        $this->report->update([
            'photos' => $photos,
            'photo_layout' => $this->sanitizePhotoLayout($layout),
        ]);

        $this->reset('newPhotos');
        $this->report->refresh();
        $this->photo_layout = $this->normalizePhotoLayout();
        $this->dispatch('photo-report-photos-updated', photos: $this->photoItems());

        session()->flash('success', 'Fotos agregadas al reporte.');
        $this->skipRender();
    }

    private function defaultBody(): string
    {
        $description = $this->report->description ?: 'Sin descripcion capturada.';

        return '
            <h2>Descripcion del reporte</h2>
            <p>' . e($description) . '</p>
        ';
    }

    private function sanitizeHtml(string $html): string
    {
        return strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><h1><h2><h3><blockquote><table><thead><tbody><tr><th><td>');
    }

    private function normalizePhotoLayout(): array
    {
        return $this->sanitizePhotoLayout($this->report->photo_layout ?? []);
    }

    private function sanitizePhotoLayout(array $layout): array
    {
        $existing = collect($layout)
            ->filter(fn ($item) => is_array($item) && isset($item['path']))
            ->keyBy('path');
        $orderedPaths = collect($layout)
            ->filter(fn ($item) => is_array($item) && isset($item['path']))
            ->pluck('path')
            ->merge($this->report->photos ?? [])
            ->unique()
            ->values();

        return $orderedPaths
            ->map(function ($path) use ($existing) {
                $item = $existing->get($path, []);
                $scale = (int) ($item['scale'] ?? $item['width'] ?? 50);

                return [
                    'path' => $path,
                    'scale' => max(25, min(100, $scale)),
                    'page' => max(1, (int) ($item['page'] ?? 1)),
                    'manual_page' => (bool) ($item['manual_page'] ?? false),
                    'break_after' => (bool) ($item['break_after'] ?? false),
                    'page_date' => $this->sanitizeDate($item['page_date'] ?? null),
                    'hidden' => false,
                ];
            })
            ->values()
            ->all();
    }

    private function photoUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function sanitizeDate(?string $date): ?string
    {
        if (! $date || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        return $date;
    }

    public function render()
    {
        return view('livewire.tenders.work-photo-report-document-editor', [
            'company' => $this->report->project->branch?->company,
            'customer' => $this->report->project->customer,
            'project' => $this->report->project,
            'photoItems' => $this->photoItems(),
        ]);
    }

    private function photoItems(): array
    {
        return collect($this->photo_layout)
            ->map(fn ($item) => $item + ['url' => $this->photoUrl($item['path'])])
            ->values()
            ->all();
    }
}
