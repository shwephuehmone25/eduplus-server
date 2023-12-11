<?php

namespace App\Models;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Level extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function courses()
    {

        return $this->belongsToMany(Course::class, 'courses_levels', 'course_id','level_id');
    }

    public function categories()
    {

        return $this->belongsToMany(Category::class, 'categories_levels', 'level_id', 'category_id');
    }
}
