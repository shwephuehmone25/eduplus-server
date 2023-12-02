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
                'grade_id'  => 23,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id'    => 2,
                'name'  => 'Beginner 2',
                'grade_id'  => 23,
                'school_id' => 1,
                'is_greater' => 1
            ],
            [
                'id' => 3,
                'name' => 'Beginner 3',
                'grade_id'  => 24,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 4,
                'name' => 'Basic 1',
                'grade_id'  => 24,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 5,
                'name' => 'Basic 2',
                'grade_id'  =>25,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 6,
                'name' => 'Basic 3',
                'grade_id'  => 25,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 7,
                'name' => 'Basic 4',
                'grade_id'  => 26,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 8,
                'name' => 'Basic 5',
                'grade_id'  => 26,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 9,
                'name' => 'Basic 6',
                'grade_id'  => 27,
                'school_id' => 1,
                'is_greater'=> 0,
            ],
            [
                'id' => 10,
                'name' => 'Elementary 1',
                'grade_id'  => 27,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 11,
                'name' => 'Elementary 2',
                'grade_id'  => 28,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 12,
                'name' => 'Elementary 3',
                'grade_id'  => 28,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 13,
                'name' => 'Elementary 4',
                'grade_id'  => 29,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 14,
                'name' => 'Elementary 5',
                'grade_id'  => 29,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 15,
                'name' => 'Elementary 6',
                'grade_id'  => 31,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 16,
                'name' => 'Intermediate 1',
                'grade_id'  => 31,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 17,
                'name' => 'Intermediate 2',
                'grade_id'  => 32,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 18,
                'name' => 'Intermediate 3',
                'grade_id'  => 32,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 19,
                'name' => 'Intermediate 4',
                'grade_id'  => 33,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 20,
                'name' => 'Intermediate 5',
                'grade_id'  => 33,
                'school_id' => 1,
                'is_greater'=> 1
            ],
            [
                'id' => 21,
                'name' => 'Intermediate 6',
                'grade_id'  => 34,
                'school_id' => 1,
                'is_greater'=> 0
            ],
            [
                'id' => 22,
                'name' => 'Upper Intermediate  1',
                'grade_id'  => 34,
                'school_id' => 1,
                'is_greater'=> 1
            ],
        ];
        TestLevel::insert($testLevels);
    }
}
