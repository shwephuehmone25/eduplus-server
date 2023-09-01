<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'course_id'];

    public function courses()
    {
        
        return $this->hasMany(Course::class);
    }
}
