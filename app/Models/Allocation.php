<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = ['course_id', 'rank_id', 'section_id', 'teacher_id', 'meeting_id'];

    public function meetings()
    {

        return $this->belongsToMany(Meeting::class, 'allocations_meetings', 'allocation_id', 'meeting_id');
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
}
