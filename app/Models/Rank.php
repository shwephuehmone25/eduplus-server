<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Section;
use App\Models\Course;
use App\Models\User;

class Rank extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function sections()
    {

        return $this->belongsToMany(Section::class, 'sections_ranks', 'rank_id', 'section_id');
    }

    public function courses()
    {

        return $this->belongsToMany(Course::class, 'courses_ranks', 'rank_id', 'course_id');
    }

    public function allocations()
    {

        return $this->hasMany(Allocation::class);
    }

    public function students()
    {

        return $this->belongsToMany(User::class, 'students_modules', 'rank_id', 'user_id');
    }
}
