<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->id();
                $table->string('email')->unique();
                $table->string('status', 20)->default('active');
                $table->boolean('consent')->default(true);
                $table->string('source', 50)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });

            return;
        }

        if (Schema::hasColumn('newsletter_subscribers', 'ip')
            && ! Schema::hasColumn('newsletter_subscribers', 'ip_address')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->string('ip_address', 45)->nullable()->after('source');
            });

            DB::table('newsletter_subscribers')
                ->whereNull('ip_address')
                ->update(['ip_address' => DB::raw('ip')]);
        }

        if (! Schema::hasColumn('newsletter_subscribers', 'consent')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->boolean('consent')->default(true)->after('status');
            });
        }

        if (! Schema::hasColumn('newsletter_subscribers', 'source')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->string('source', 50)->nullable()->after('consent');
            });
        }

        if (! Schema::hasColumn('newsletter_subscribers', 'user_agent')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->text('user_agent')->nullable()->after(
                    Schema::hasColumn('newsletter_subscribers', 'ip_address') ? 'ip_address' : 'source',
                );
            });
        }

        if (! Schema::hasColumn('newsletter_subscribers', 'created_at')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('newsletter_subscribers', 'updated_at')) {
            Schema::table('newsletter_subscribers', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        // Pas de rollback destructif sur une table legacy potentiellement peuplée.
    }
};
