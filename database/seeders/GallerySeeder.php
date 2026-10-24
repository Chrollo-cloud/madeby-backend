<?php

namespace Database\Seeders;

use App\Models\Listing;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $galleries = [
            'studio-portrait-session' => ['studio-5.jpg', 'studio-6.jpg', 'avocaglue.jpg', 'photography.jpg', 'studio-photography.jpg'],
            'custom-poster-design' => ['digital-poster.jpg', 'iced-drinks.jpg', 'matcha-latte-japanese-flyer.jpg'],
            'campus-editorial-session' => ['ig-harrrriet-p.jpg', 'campus-photography.jpg', 'campus-8.jpg'],
            'custom-typography-design' => ['typography-5.jpg', 'lettering-lyrics-poster.jpg', 'typography-design.jpg'],
            'custom-keychain' => ['accesoris.jpg', 'keychain-7.jpg', 'cute-cat-keychain.jpg'],
            'custom-digital-artwork' => ['instagram.jpg', 'digital-art-6.jpg', 'stamp-art.jpg', 'girl-flat-illustration.jpg', 'burger-art.jpg'],
        ];
        foreach ($galleries as $slug => $files) {
            $listing = Listing::where('slug', $slug)->first();
            if (! $listing) {
                continue;
            }
            foreach ($files as $file) {
                $listing->images()->firstOrCreate(['path' => 'listings/'.$file]);
            }
        }
    }
}
