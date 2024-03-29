<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentSection extends Model
{
    use HasFactory;

    protected $table = 'students_sections';
    protected $fillable = ['user_id', 'course_id'];
}
