<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BellSchedule extends Model
{
    use HasUuids;

    protected $fillable = [
        'bell_type_id',
        'day',
        'time',
        'audio_library_id',
        'keterangan',
    ];

    public function bellType(): BelongsTo
    {
        return $this->belongsTo(BellType::class);
    }

    public function audioLibrary(): BelongsTo
    {
        return $this->belongsTo(AudioLibrary::class);
    }
}
