<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BellType extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'is_active',
        'active_days',
        'is_automatic',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'active_days' => 'array',
        'is_automatic' => 'boolean',
    ];

    public function bellSchedules(): HasMany
    {
        return $this->hasMany(BellSchedule::class);
    }
}
