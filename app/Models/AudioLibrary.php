<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AudioLibrary extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'file_path',
    ];

    public function bellSchedules(): HasMany
    {
        return $this->hasMany(BellSchedule::class);
    }
}
