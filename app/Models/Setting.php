<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasUuids;

    private const CACHE_KEY = 'settings.all';

    protected $fillable = [
        'key',
        'value',
    ];

    protected static function booted(): void
    {
        // Setiap perubahan setting langsung membuang cache
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /**
     * Semua setting dalam satu query, di-cache (TTL pendek sebagai pengaman
     * jika tabel diubah di luar Eloquent, mis. restore backup).
     */
    public static function allCached(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () {
            return self::query()->pluck('value', 'key')->all();
        });
    }

    public static function get(string $key, $default = null)
    {
        try {
            $all = self::allCached();
        } catch (\Throwable $e) {
            // Cache bermasalah: fallback langsung ke database
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        }

        return array_key_exists($key, $all) && $all[$key] !== null ? $all[$key] : $default;
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        static::flushCache();
    }

    public static function flushCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable $e) {
            // abaikan
        }
    }
}
