<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;
/**
 * Class BannerTableSeeder.
 */
class BannerSeeder extends Seeder
{
    /**
     * Run the database seed.
     */
    public function run(): void
    {
        Banner::create([
            'title' => trans("modules/banners/seeders.Start your consultation now and stop worrying"),
            'url' => 'https://www.google.com/',
            'description'=> trans("modules/banners/seeders.Our biggest mistakes against ourselves are anxiety, succumbing to depression, and feeling frustrated.")
        ]);
        Banner::create([
            'title' => trans("modules/banners/seeders.Procrastination will not change your situation"),
            'url' => 'https://www.google.com/',
            'description'=> trans("modules/banners/seeders.Talbina offers you her advisors to provide you with psychological comfort")
        ]);
        Banner::create([
            'title' => trans("modules/banners/seeders.-Does fear still prevent you from moving forward?"),
            'url' => 'https://www.google.com/',
            'description'=> trans("modules/banners/seeders.'Call our consultants  now and take medical advice.")
        ]);

        
    }
}
