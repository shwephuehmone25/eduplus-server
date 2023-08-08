<?php

namespace App\Models;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Video extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = ['title','url','duration'];

    public function courses()
    {

        return $this->belongsToMany(Course::class,'courses_videos', 'video_id','course_id');
    }

    public function categories()
    {

        return $this->belongsToMany(Category::class,'videos_categories','video_id', 'category_id');
    }
}
