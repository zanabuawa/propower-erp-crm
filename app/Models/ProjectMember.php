<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'role',
        'is_active', 'joined_at', 'left_at', 'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'joined_at' => 'date',
        'left_at'   => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}