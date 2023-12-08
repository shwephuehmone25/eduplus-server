<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Meeting;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\Category;
use App\Models\Teacher;
use App\Models\Rank;
use App\Models\Like;
use App\Models\Wishlist;
use App\Models\Section;
use App\Contracts\Likeable;
use App\Models\Concerns\Likes;
use App\Models\Payment;

class Allocation extends Model implements Likeable
{
    use HasFactory, Likes;

    protected $table = 'allocations';
    protected $primaryKey = 'id';

    protected $fillable = ['course_id', 'rank_id', 'section_id', 'teacher_id', 'classroom_id', 'course_type', 'status', 'capacity'];

    public function meetings()
    {

        return $this->belongsToMany(Meeting::class, 'allocations_meetings', 'allocation_id', 'meeting_id');
    }

    public function users()
    {

        return $this->belongsToMany(User::class, 'students_allocations', 'allocation_id', 'user_id');
    }

    public function teacher()
    {

        return $this->belongsTo(Teacher::class);
    }

    public function course()
    {

        return $this->belongsTo(Course::class);
    }

    public function rank()
    {

        return $this->belongsTo(Rank::class);
    }

    public function section()
    {

        return $this->belongsTo(Section::class);
    }

    public function classroom()
    {

        return $this->belongsTo(Classroom::class);
    }

    public function likedCourses(): MorphMany
    {

        return $this->morphMany(Like::class, 'likeable');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'courses_categories', 'course_id', 'category_id');
    }

    public function wishlist()
    {
        return $this->belongsToMany(User::class, 'wishlists', 'allocation_id', 'user_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
