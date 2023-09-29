<?php

namespace App\Models;

use App\Models\User;
use App\Models\Level;
use App\Models\Rank;
use App\Models\Like;
use App\Models\Policy;
use App\Models\Meeting;
use App\Models\Teacher;
use App\Models\Category;
use App\Models\Classroom;
use App\Models\Enrollment;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Contracts\Likeable;
use App\Models\Concerns\Likes;

class Course extends Model implements Likeable
{
    use HasFactory, SoftDeletes, Likes;

    protected $fillable = ['course_name', 'description', 'period'];

    public function categories()
    {

        return $this->belongsToMany(Category::class, 'courses_categories', 'course_id', 'category_id');
    }

    public function levels()
    {

        return $this->belongsToMany(Level::class, 'courses_levels', 'course_id','level_id');
    }

    public function sections()
    {

        return $this->hasMany(Section::class);
    }

    public function students()
    {

        return $this->belongsToMany(User::class, 'students_sections', 'course_id','user_id');
    }

    public function enrollments()
    {

        return $this->belongsToMany(Enrollment::class, 'courses_enrollments', 'course_id','enrollment_id');
    }

    public function classrooms()
    {

        return $this->belongsToMany(Classroom::class,'courses_classrooms', 'course_id','classroom_id');
    }

    public function teachers()
    {

        return $this->belongsToMany(Teacher::class,'teacher_courses', 'course_id','teacher_id');
    }

    public function subcategories()
    {

        return $this->belongsToMany(Subcategory::class, 'courses_subcategories', 'course_id', 'subcategory_id');
    }

    public function meetings()
    {

        return $this->belongsToMany(Meeting::class, 'meeting_courses', 'course_id',  'meeting_id');
    }

    public function videos()
    {

        return $this->belongsToMany(Video::class,'courses_videos', 'course_id','video_id');
    }

    public function policy()
    {

        return $this->hasOne(Policy::class);
    }

    public function likedCourses(): MorphMany
    {

        return $this->morphMany(Like::class, 'likeable');
    }

    public function ranks()
    {

        return $this->belongsToMany(Rank::class, 'courses_ranks', 'course_id', 'rank_id');
    }

    public function allocations()
    {

        return $this->hasMany(Allocation::class);
    }

    public function images()
    {

        return $this->morphMany(Image::class, 'imageable');
    }
}
