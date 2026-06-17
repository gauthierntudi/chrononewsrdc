<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Setting extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return Schema::hasTable('global_settings') ? 'global_settings' : 'settings';
    }

    public function usesTimestamps(): bool
    {
        return Schema::hasTable('settings');
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        if (Schema::hasTable('global_settings')) {
            return DB::table('global_settings')
                ->where('setting_key', $key)
                ->value('setting_value') ?? $default;
        }

        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        if (Schema::hasTable('global_settings')) {
            $exists = DB::table('global_settings')->where('setting_key', $key)->exists();

            if ($exists) {
                DB::table('global_settings')
                    ->where('setting_key', $key)
                    ->update(['setting_value' => $value]);
            } else {
                DB::table('global_settings')->insert([
                    'setting_key' => $key,
                    'setting_value' => $value,
                ]);
            }

            return;
        }

        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    /** @return array<string, mixed> */
    public static function allAsMap(): array
    {
        if (Schema::hasTable('global_settings')) {
            return DB::table('global_settings')
                ->pluck('setting_value', 'setting_key')
                ->all();
        }

        return static::query()->pluck('value', 'key')->all();
    }
}
