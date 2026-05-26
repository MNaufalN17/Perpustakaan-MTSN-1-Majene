<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
        'type',
        'setting_group',
        'description',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function intValue(string $key, int $default = 0): int
    {
        return (int) static::getValue($key, $default);
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
            ]
        );
    }
}