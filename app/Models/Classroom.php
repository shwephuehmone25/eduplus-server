<?php

namespace App\Models;

use App\Models\Meeting;
use App\Models\Allocation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description','start_date','end_date','capacity'];

    public function courses()
    {

        return $this->belongsToMany(Course::class ,'courses_classrooms','classroom_id', 'course_id');
    }

    public function allocations()
    {

        return $this->hasMany(Allocation::class);
    }

    public function sections()
    {

        return $this->belongsToMany(Section::class, 'classroom_sections', 'classroom_id', 'section_id');
    }
}
