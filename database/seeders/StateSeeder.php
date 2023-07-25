<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        State::truncate();

        $states = [
            ['name' => 'Ka Chin'],
            ['name' => 'Ka Yah'],
            ['name' => 'Ka Yin'],
            ['name' => 'Chin'],
            ['name' => 'Mon'],
            ['name' => 'Yakhine'],
            ['name' => 'Shan'],
            ['name' => 'Yangon'],
            ['name' => 'Sagaing'],
            ['name' => 'Magway'],
            ['name' => 'Mandalay'],
            ['name' => 'Tanintharyi'],
            ['name' => 'Bago'],
            ['name' => 'Ayeyarwaddy'],
        ];
          
        foreach ($states as $key => $value) {
            State::create($value);
        }
    }
}
