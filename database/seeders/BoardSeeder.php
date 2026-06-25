<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Board;

/**
 * Class BoardTableSeeder.
 */
class BoardSeeder extends Seeder
{
    /**
     * Run the database seed.
     */
    public function run(): void
    {
        Board::create([
            'description'=> trans("modules/boards/seeders.Thousands of doctors & experts to help your health!")
        ]);
        Board::create([
            'description'=> trans("modules/boards/seeders.Health checks & consultations easily anywhere anytime")
        ]);
        Board::create([
            'description'=> trans("modules/boards/seeders.Lets start living healthy and well with us right now!")
        ]);

    }
}
