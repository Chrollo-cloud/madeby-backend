<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (['ART', 'FASHION', 'PHOTOGRAPHY', 'DESIGN', 'ACCESSORIES', 'STICKERS', 'DIGITAL'] as $name) {
            \App\Models\Category::firstOrCreate(['name' => $name]);
        }
    }
}
