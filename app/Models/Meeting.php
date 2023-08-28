<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\Teacher;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = ['start_time', 'end_time', 'meet_link'];

    public function courses()
    {
        
        return $this->belongsToMany(Course::class, 'meeting_courses', 'meeting_id', 'course_id');
    }

    public function teacher()
    {

        return $this->belongsTo(Teacher::class);
    }
}
