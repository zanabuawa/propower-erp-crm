<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingSetting extends Model
{
    protected $fillable = ['section', 'data'];

    protected $casts = ['data' => 'array'];

    public static function getSection(string $section, array $default = []): array
    {
        return static::where('section', $section)->value('data') ?? $default;
    }

    public static function setSection(string $section, array $data): void
    {
        static::updateOrCreate(['section' => $section], ['data' => $data]);
    }

    public static function allSections(): array
    {
        return static::all()->pluck('data', 'section')->toArray();
    }
}
