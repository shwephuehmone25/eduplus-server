<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherCourse extends Model
{
    use HasFactory;

    protected $table = 'teachers_sectionss';
    protected $fillable = ['teacher_id', 'section_id'];
}
