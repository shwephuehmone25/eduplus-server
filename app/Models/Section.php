<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Allocation;
use App\Models\Meeting;
use App\Models\Teacher;
use App\Models\Rank;
use Illuminate\Database\Eloquent\SoftDeletes;

class Section extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'start_time', 'end_time', 'capacity'];

    public function courses()
    {

        return $this->belongsToMany(Course::class, 'course_sections', 'section_id', 'course_id');
    }

    public function meetings()
    {

        return $this->belongsToMany(Meeting::class, 'meeting_sections', 'section_id',  'meeting_id');
    }

    public function teachers()
    {

        return $this->belongsToMany(Teacher::class,'teachers_sections', 'section_id','teacher_id');
    }

    public function students()
    {

        return $this->belongsToMany(User::class, 'students_sections', 'section_id','user_id');
    }

    public function ranks()
    {
        return $this->belongsToMany(Rank::class, 'sections_ranks', 'section_id' , 'rank_id');
    }

    public function allocations()
    {
        return $this->hasMany(Allocation::class);
    }
}
