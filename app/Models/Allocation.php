<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Rank;
use App\Models\Section;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'rank_id', 'section_id', 'teacher_id'];
}
