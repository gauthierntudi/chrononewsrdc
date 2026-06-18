<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('global_settings')) {
            return;
        }

        $exists = DB::table('global_settings')
            ->where('setting_key', 'breaking_news_enabled')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('global_settings')->insert([
            'setting_key' => 'breaking_news_enabled',
            'setting_value' => '1',
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('global_settings')) {
            return;
        }

        DB::table('global_settings')
            ->where('setting_key', 'breaking_news_enabled')
            ->delete();
    }
};
