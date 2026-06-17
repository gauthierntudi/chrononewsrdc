<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Répare un déploiement partiel où `advertisements` existe sans index (nom auto trop long).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        if ($this->indexExists('advertisements', 'ads_status_lookup_idx')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->index(['broadcast_status', 'validation_status', 'payment_status'], 'ads_status_lookup_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('advertisements')) {
            return;
        }

        if (! $this->indexExists('advertisements', 'ads_status_lookup_idx')) {
            return;
        }

        Schema::table('advertisements', function (Blueprint $table) {
            $table->dropIndex('ads_status_lookup_idx');
        });
    }

    protected function indexExists(string $table, string $indexName): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$indexName]);

        return count($rows) > 0;
    }
};
