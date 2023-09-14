<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subcategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function categories()
    {

        return $this->belongsToMany(Category::class, 'categories_subcategories', 'subcategory_id', 'category_id');
    }

    public function courses()
    {

        return $this->belongsToMany(Course::class, 'courses_subcategories', 'course_id', 'subcategory_id');
    }
}
