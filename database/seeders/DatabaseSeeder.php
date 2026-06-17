<?php

namespace Database\Seeders;

use App\Enums\ArticleCategory;
use App\Enums\UserRole;
use App\Models\AdvertisementRate;
use App\Models\Setting;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Services\Admin\SocialMediaSettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->updateOrCreate(
            ['mail' => env('SEED_SUPERADMIN_EMAIL', 'admin@fintechmedias.cd')],
            [
                'nom' => 'Super Admin',
                'num_user' => 'USR'.time(),
                'role' => UserRole::SuperAdmin,
                'status' => 1,
                'cover' => '',
                'mdp' => '',
                'connect' => 0,
            ],
        );

        Setting::setValue('default_article_price', (string) config('chrononews.article.default_price'));
        Setting::setValue('site_categories', json_encode(ArticleCategory::values()));
        Setting::setValue(
            'social_media',
            json_encode(app(SocialMediaSettingsService::class)->defaults(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        );

        if (Schema::hasTable('subscription_plans')) {
            SubscriptionPlan::query()->updateOrCreate(
                ['name' => 'Mensuel'],
                [
                    'duration_days' => 30,
                    'price' => 9.99,
                    'description' => 'Accès illimité aux articles premium pendant 30 jours',
                    'is_active' => true,
                ],
            );
        }

        if (Schema::hasTable('advertisement_rates')) {
            $adFormats = [
                ['format' => 'rectangle', 'label' => 'Rectangle', 'dimensions' => '672x560', 'price_7_days' => 50, 'price_15_days' => 90, 'price_30_days' => 150],
                ['format' => 'portrait', 'label' => 'Portrait', 'dimensions' => '512x562', 'price_7_days' => 40, 'price_15_days' => 75, 'price_30_days' => 120],
                ['format' => 'paysage_small', 'label' => 'Bannière small', 'dimensions' => '1456x180', 'price_7_days' => 80, 'price_15_days' => 140, 'price_30_days' => 220],
                ['format' => 'paysage_medium', 'label' => 'Bannière medium', 'dimensions' => '1920x400', 'price_7_days' => 120, 'price_15_days' => 200, 'price_30_days' => 320],
                ['format' => 'paysage_large', 'label' => 'Bannière large', 'dimensions' => '3456x502', 'price_7_days' => 200, 'price_15_days' => 350, 'price_30_days' => 550],
            ];

            foreach ($adFormats as $format) {
                AdvertisementRate::query()->updateOrCreate(
                    ['format' => $format['format']],
                    $format,
                );
            }
        }

        $this->command?->info("Super admin: {$superAdmin->email}");
        $this->command?->info('Rubriques: '.count(ArticleCategory::values()).' catégories enregistrées.');
    }
}
