<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSection extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $table = 'teachers_sections';
    protected $fillable = ['teacher_id', 'section_id', 'course_id'];
}
