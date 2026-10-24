<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([CategorySeeder::class, CreatorSeeder::class, ListingSeeder::class, GallerySeeder::class]);
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@madeby.test'],
            ['name' => 'MADEBY Admin', 'is_admin' => true,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(48))]
        );
    }
}
