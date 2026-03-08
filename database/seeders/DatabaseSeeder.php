<?php

namespace Database\Seeders;

use App\Models\Domain;
use App\Models\LicenseKey;
use App\Models\Plugin;
use App\Models\PluginVersion;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user for Filament dashboard
        User::factory()->create([
            'name' => 'Digsan Admin',
            'email' => 'admin@digsan.id',
            'password' => Hash::make('password'),
        ]);

        // Managed plugins
        $plugins = [
            [
                'name' => 'MN Effects',
                'slug' => 'mn-effects',
                'file_slug' => 'mn-effects/mn-effects.php',
                'description' => 'Interactive animations and effects hub for Elementor widgets including text animations, image effects, and more.',
                'homepage' => 'https://www.digsan.id/mn-effects',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
            [
                'name' => 'MN Elements',
                'slug' => 'mn-elements',
                'file_slug' => 'mn-elements/mn-elements.php',
                'description' => 'Kumpulan widget dan efek kustom untuk Elementor yang dapat memperkaya halaman web Anda dengan animasi dan kontrol yang menarik.',
                'homepage' => 'https://www.digsan.id/mn-elements',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
            [
                'name' => 'MN Menu Booking',
                'slug' => 'mn-menu-booking',
                'file_slug' => 'mn-menu-booking/mn-menu-booking.php',
                'description' => 'WooCommerce Menu Product Type for restaurant menu ordering with comprehensive configuration fields compatible with ACF and JetEngine.',
                'homepage' => 'https://www.digsan.id/mn-menu-booking',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
            [
                'name' => 'MN WooEngine',
                'slug' => 'mn-woo-engine',
                'file_slug' => 'mn-woo-engine/mn-woo-engine.php',
                'description' => 'Advanced WooCommerce engine with Cookie Consent, Advanced Cart, Coupon & Price Policy, Wishlist, Multicurrency, Payment Control, Shipping Control, and Jubelio Sync.',
                'homepage' => 'https://www.digsan.id/mn-woo-engine',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
            [
                'name' => 'MN Account & Dashboard',
                'slug' => 'mna-account',
                'file_slug' => 'mna-account/mna-account.php',
                'description' => 'A comprehensive account management plugin providing registration, login, dashboard, and notification system for WordPress users with WooCommerce and Elementor integration.',
                'homepage' => 'https://www.digsan.id/mna-account',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
            [
                'name' => 'MN Updater',
                'slug' => 'mn-updater',
                'file_slug' => 'mn-updater/mn-updater.php',
                'description' => 'Centralized update manager for Digsan-Id plugins. Automatically checks and delivers updates from the Digsan-Id update server.',
                'homepage' => 'https://www.digsan.id/mn-updater',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
            [
                'name' => 'MN CPT',
                'slug' => 'mn-cpt',
                'file_slug' => 'mn-cpt/mn-cpt.php',
                'description' => 'Custom Post Types and Taxonomies manager for WordPress with advanced configuration options.',
                'homepage' => 'https://www.digsan.id/mn-cpt',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
            [
                'name' => 'MN Tour Booking',
                'slug' => 'mn-tour-booking',
                'file_slug' => 'mn-tour-booking/mn-tour-booking.php',
                'description' => 'Tour and travel booking system for WordPress with WooCommerce integration.',
                'homepage' => 'https://www.digsan.id/mn-tour-booking',
                'requires_php' => '7.4',
                'requires_wp' => '5.8',
                'tested_wp' => '6.7',
            ],
        ];

        foreach ($plugins as $pluginData) {
            $plugin = Plugin::create(array_merge($pluginData, [
                'author' => 'Digsan-Id',
                'author_uri' => 'https://www.digsan.id/',
                'is_active' => true,
            ]));

            // Create initial version 1.0
            PluginVersion::create([
                'plugin_id' => $plugin->id,
                'version' => '1.0',
                'changelog' => "### 1.0\n- Initial release",
                'requires_php' => $pluginData['requires_php'],
                'requires_wp' => $pluginData['requires_wp'],
                'tested_wp' => $pluginData['tested_wp'],
                'released_at' => now(),
            ]);
        }

        // Sample license key
        $license = LicenseKey::create([
            'key' => 'TEST-AAAA-BBBB-CCCC',
            'client_name' => 'Test Client',
            'client_email' => 'test@example.com',
            'max_domains' => 5,
            'is_active' => true,
            'expires_at' => null,
            'notes' => 'Test license for development',
        ]);

        // Sample domain
        Domain::create([
            'license_key_id' => $license->id,
            'domain' => 'localhost',
            'site_url' => 'http://localhost',
            'is_active' => true,
            'registered_at' => now(),
        ]);
    }
}
