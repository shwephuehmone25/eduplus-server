<?php

namespace App\Models;

use App\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    public function courses()
    {
       
        return $this->belongsToMany(Course::class, 'courses_categories');
    }

    public function videos(){
        return $this->belongsToMany(Video::class, 'video_categories', 'video_id', 'category_id');
    }
}
