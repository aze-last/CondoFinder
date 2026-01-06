<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        // 1. Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@condofinder.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'), // Or secure password
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->assignRole('Super Admin');

        // 2. Owner
        $owner = User::firstOrCreate(
            ['email' => 'owner@condofinder.com'],
            [
                'name' => 'Bien Demo Site',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'phone' => '09171234567',
                'slug' => 'condo-owner',
                'is_active' => true,
            ]
        );
        $owner->assignRole('Owner');

        // 3. Mock Listings for Owner
        $category = \App\Models\Category::create([
            'owner_id' => $owner->id,
            'name' => 'Beachfront',
            'slug' => 'beachfront',
        ]);

        $listing = \App\Models\Listing::create([
            'owner_id' => $owner->id,
            'title' => 'Luxury Condo with Sea View',
            'slug' => 'luxury-condo-sea-view',
            'description' => 'Beautiful condo with amazing views.',
            'price_per_night' => 5000.00,
            'location_text' => 'Mactan Newtown, Cebu',
            'status' => 'AVAILABLE',
        ]);
        
        $listing->categories()->attach($category->id);

        \App\Models\Inquiry::create([
            'listing_id' => $listing->id,
            'owner_id' => $owner->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'message' => 'Is this available next week?',
        ]);
    }
}
