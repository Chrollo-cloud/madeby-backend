<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CreatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            ['maylin', 'Maylin', 'Creative Director', 'Bandung, ID', 'preorder-graphic-tee.jpg'],
            ['nara', 'Nara', 'Graphic Designer', 'Yogyakarta, ID', 'digital-poster.jpg'],
            ['mira', 'Mira', 'Photographer', 'Jakarta, ID', 'studio-5.jpg'],
            ['pim', 'Pim', 'Illustrator', 'Surabaya, ID', 'instagram.jpg'],
            ['luna', 'Luna', 'Digital Designer', 'Malang, ID', 'car-wash-ui-kit.jpg'],
            ['aira', 'Aira', 'Accessories Artist', 'Denpasar, ID', 'accesoris.jpg']
        ];
        foreach ($rows as $c) {
            \App\Models\Creator::firstOrCreate(['slug' => $c[0]], [
                'name' => $c[1],
                'role' => $c[2],
                'bio' => 'Student creative making thoughtful work for campus life.',
                'location' => $c[3],
                'avatar' => 'listings/'.$c[4],
                'followers' => '5k',
                'likes' => '20k']);
        }
    }
}
