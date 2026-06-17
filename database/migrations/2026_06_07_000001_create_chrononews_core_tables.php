<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tables Laravel (schéma normalisé).
 * Pas de clés étrangères : la BDD legacy utilise MyISAM sur `users`.
 * Chaque table est créée seulement si elle n'existe pas déjà.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('articles')) {
            Schema::create('articles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('updated_by')->nullable()->index();
                $table->string('title');
                $table->longText('content')->nullable();
                $table->text('cover')->nullable();
                $table->string('caption')->nullable();
                $table->text('videos')->nullable();
                $table->string('category', 50);
                $table->string('payment_status', 20)->default('pending');
                $table->string('validation_status', 20)->default('pending');
                $table->boolean('is_published')->default(false);
                $table->string('article_number', 32)->unique();
                $table->string('post_type', 20)->default('article');
                $table->boolean('is_featured')->default(false);
                $table->unsignedBigInteger('views')->default(0);
                $table->boolean('is_premium')->default(false);
                $table->decimal('price', 10, 2)->nullable();
                $table->timestamp('published_at')->nullable();
                $table->timestamps();

                $table->index(['is_published', 'validation_status', 'payment_status'], 'articles_status_lookup_idx');
                $table->index(['category', 'published_at'], 'articles_category_published_idx');
                $table->index('is_featured', 'articles_featured_idx');
            });
        }

        if (! Schema::hasTable('article_blocks')) {
            Schema::create('article_blocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('article_id')->index();
                $table->string('block_number', 32)->unique();
                $table->string('title')->nullable();
                $table->longText('content')->nullable();
                $table->text('cover')->nullable();
                $table->string('caption')->nullable();
                $table->text('videos')->nullable();
                $table->string('post_type', 20)->default('article');
                $table->boolean('is_active')->default(true);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subscription_plans')) {
            Schema::create('subscription_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('duration_days');
                $table->decimal('price', 10, 2);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertisement_rates') && ! Schema::hasTable('tarifs_publicites')) {
            Schema::create('advertisement_rates', function (Blueprint $table) {
                $table->id();
                $table->string('format', 50)->unique();
                $table->string('label');
                $table->string('dimensions', 20);
                $table->decimal('price_7_days', 10, 2)->default(0);
                $table->decimal('price_15_days', 10, 2)->default(0);
                $table->decimal('price_30_days', 10, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('advertisements') && ! Schema::hasTable('publicites')) {
            Schema::create('advertisements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('title');
                $table->string('format', 50);
                $table->string('placement', 50)->nullable();
                $table->string('image_url');
                $table->string('target_url');
                $table->string('dimensions', 20)->nullable();
                $table->date('starts_at');
                $table->date('ends_at');
                $table->decimal('amount_paid', 10, 2)->default(0);
                $table->string('payment_status', 20)->default('pending');
                $table->string('validation_status', 20)->default('pending');
                $table->string('broadcast_status', 20)->default('inactive');
                $table->boolean('is_locked')->default(false);
                $table->boolean('created_by_admin')->default(false);
                $table->unsignedBigInteger('impressions')->default(0);
                $table->unsignedBigInteger('clicks')->default(0);
                $table->timestamps();

                $table->index(['broadcast_status', 'validation_status', 'payment_status'], 'ads_status_lookup_idx');
            });
        }

        if (! Schema::hasTable('payments') && ! Schema::hasTable('paiements')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('article_id')->nullable()->index();
                $table->unsignedBigInteger('subscription_plan_id')->nullable()->index();
                $table->unsignedBigInteger('advertisement_id')->nullable()->index();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('USD');
                $table->string('method', 30);
                $table->string('transaction_id', 64)->unique();
                $table->string('gateway_reference', 128)->nullable()->index();
                $table->string('status', 20)->default('pending');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('user_subscriptions')) {
            Schema::create('user_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('subscription_plan_id')->index();
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->timestamp('starts_at');
                $table->timestamp('ends_at');
                $table->string('status', 20)->default('active');
                $table->timestamps();

                $table->index(['user_id', 'status', 'ends_at'], 'user_subs_active_lookup_idx');
            });
        }

        if (! Schema::hasTable('article_purchases') && ! Schema::hasTable('user_purchased_articles')) {
            Schema::create('article_purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->unsignedBigInteger('article_id')->index();
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->decimal('amount', 10, 2);
                $table->string('access_status', 20)->default('active');
                $table->timestamps();

                $table->unique(['user_id', 'article_id']);
            });
        }

        if (! Schema::hasTable('home_videos') && ! Schema::hasTable('home_video')) {
            Schema::create('home_videos', function (Blueprint $table) {
                $table->id();
                $table->string('youtube_id', 50);
                $table->string('title');
                $table->string('subtitle')->nullable();
                $table->string('website_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('settings') && ! Schema::hasTable('global_settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('article_purchases');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('advertisements');
        Schema::dropIfExists('advertisement_rates');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('article_blocks');
        Schema::dropIfExists('articles');
    }
};
