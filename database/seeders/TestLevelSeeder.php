<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TestLevel;

class TestLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $testLevels = [
            [
                'id'    => 1, 
                'name'  => 'Beginner 1',
                'grade_id'  => 1,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id'    => 2,
                'name'  => 'Beginner 2',
                'grade_id'  => 1,
                'school_id' => 1,
                'is_greater' => 1
            ],
            [
                'id' => 3,
                'name' => 'Beginner 3',
                'grade_id'  => 2,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 4,
                'name' => 'Basic 1',
                'grade_id'  => 2,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 5,
                'name' => 'Basic 2',
                'grade_id'  => 3,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 6,
                'name' => 'Basic 3',
                'grade_id'  => 3,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 7,
                'name' => 'Basic 4',
                'grade_id'  => 4,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 8,
                'name' => 'Basic 5',
                'grade_id'  => 4,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 9,
                'name' => 'Basic 6',
                'grade_id'  => 5,
                'school_id' => 1,
                'is_greater'=> 0,
            ],
            [
                'id' => 10,
                'name' => 'Elementary 1',
                'grade_id'  => 5,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 11,
                'name' => 'Elementary 2',
                'grade_id'  => 6,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 12,
                'name' => 'Elementary 3',
                'grade_id'  => 6,
                'school_id' => 1,
                'is_greater'=> 1
            ],
        ];
        TestLevel::insert($testLevels);
    }
}
