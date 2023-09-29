<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentModule extends Model
{
    use HasFactory;

    protected $table = 'students_modules';
    protected $fillable = ['user_id', 'course_id', 'rank_id', 'is_complete', 'end_date'];
}
