<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('actualites') && Schema::hasColumn('actualites', 'categorie')) {
            DB::table('actualites')
                ->where('categorie', 'Société')
                ->update(['categorie' => 'Culture & Société']);
        }

        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'category')) {
            DB::table('articles')
                ->where('category', 'Société')
                ->update(['category' => 'Culture & Société']);
        }

        if (Schema::hasTable('global_settings')) {
            $raw = DB::table('global_settings')->where('setting_key', 'site_categories')->value('setting_value');
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $updated = array_map(
                        static fn (mixed $item): mixed => $item === 'Société' ? 'Culture & Société' : $item,
                        $decoded,
                    );
                    DB::table('global_settings')
                        ->where('setting_key', 'site_categories')
                        ->update(['setting_value' => json_encode(array_values(array_unique($updated)), JSON_UNESCAPED_UNICODE)]);
                }
            }
        }

        if (Schema::hasTable('settings')) {
            $raw = DB::table('settings')->where('key', 'site_categories')->value('value');
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $updated = array_map(
                        static fn (mixed $item): mixed => $item === 'Société' ? 'Culture & Société' : $item,
                        $decoded,
                    );
                    DB::table('settings')
                        ->where('key', 'site_categories')
                        ->update(['value' => json_encode(array_values(array_unique($updated)), JSON_UNESCAPED_UNICODE)]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('actualites') && Schema::hasColumn('actualites', 'categorie')) {
            DB::table('actualites')
                ->where('categorie', 'Culture & Société')
                ->update(['categorie' => 'Société']);
        }

        if (Schema::hasTable('articles') && Schema::hasColumn('articles', 'category')) {
            DB::table('articles')
                ->where('category', 'Culture & Société')
                ->update(['category' => 'Société']);
        }
    }
};
