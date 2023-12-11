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

    public function sections()
    {

        return $this->belongsToMany(Section::class, 'meeting_sections', 'meeting_id', 'section_id');
    }

    public function teacher()
    {

        return $this->belongsTo(Teacher::class);
    }

    public function allocations()
    {

        return $this->belongsToMany(Allocation::class, 'allocations_meetings', 'meeting_id', 'allocation_id');
    }
}
