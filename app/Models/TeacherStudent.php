<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherStudent extends Model
{
    use HasFactory;

    protected $fillable = ['teacher_id', 'user_id'];

    protected $table = 'teachers_students';
}
