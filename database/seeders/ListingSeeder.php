<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            ['preorder-graphic-tee', 'product', 'Preorder Graphic Tee', 'FASHION', 'maylin', 165000, 2, 'preorder-graphic-tee.jpg'],
            ['sticker-pack', 'product', 'Sticker Pack', 'STICKERS', 'pim', 45000, 12, 'sticker-pack.jpg'],
            ['car-wash-ui-kit', 'product', 'Modern Car Wash UI Kit', 'DIGITAL', 'luna', 125000, 20, 'car-wash-ui-kit.jpg'],
            ['madeby-graphic-tee', 'product', 'MADEBY Graphic Tee', 'FASHION', 'maylin', 170000, 5, 'madeby-graphic-tee.jpg'],
            ['studio-portrait-session', 'commission', 'Studio Portrait Session', 'PHOTOGRAPHY', 'mira', 300000, 2, 'studio-5.jpg'],
            ['custom-poster-design', 'commission', 'Custom Poster Design', 'DESIGN', 'nara', 175000, 2, 'digital-poster.jpg'],
            ['campus-editorial-session', 'commission', 'Campus Editorial Session', 'PHOTOGRAPHY', 'mira', 250000, 3, 'ig-harrrriet-p.jpg'],
            ['custom-typography-design', 'commission', 'Custom Typography Design', 'DESIGN', 'nara', 180000, 2, 'typography-5.jpg'],
            ['custom-keychain', 'commission', 'Custom Couple Keychain', 'ACCESSORIES', 'aira', 90000, 4, 'accesoris.jpg'],
            ['custom-digital-artwork', 'commission', 'Custom Digital Artwork', 'ART', 'pim', 150000, 3, 'instagram.jpg']
        ];
        foreach ($rows as $r) {
            $l = \App\Models\Listing::firstOrCreate(['slug' => $r[0]], [
                'type' => $r[1],
                'title' => $r[2],
                'category_id' => \App\Models\Category::where('name', $r[3])->value('id'),
                'creator_id' => \App\Models\Creator::where('slug', $r[4])->value('id'),
                'price' => $r[1] === 'product' ? $r[5] : null,
                'price_from' => $r[1] === 'commission' ? $r[5] : null,
                'stock' => $r[1] === 'product' ? $r[6] : null,
                'commission_slots' => $r[1] === 'commission' ? $r[6] : null,
                'delivery_time' => $r[1] === 'commission' ? '3–7 days' : null,
                'rating' => 4.8,
                'description' => 'A student-made creative piece crafted for campus life and beyond.',
                'image' => 'listings/'.$r[7],
                'tags' => ['student', 'creative', $r[3]]]);
            $l->images()->firstOrCreate(['path' => 'listings/'.$r[7]]);
        }
    }
}
