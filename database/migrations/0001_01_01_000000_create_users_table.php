<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('user_number', 32)->unique();
                $table->string('role', 20)->default('user');
                $table->string('otp', 10)->nullable();
                $table->timestamp('otp_expires_at')->nullable();
                $table->string('avatar')->nullable();
                $table->string('job_title')->nullable();
                $table->string('phone', 30)->nullable();
                $table->text('bio')->nullable();
                $table->string('facebook')->nullable();
                $table->string('youtube')->nullable();
                $table->string('twitter')->nullable();
                $table->string('instagram')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};
