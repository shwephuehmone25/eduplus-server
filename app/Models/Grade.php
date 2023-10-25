<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Question;
use App\Models\School;
use App\Models\User;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function questions()
    {

        return $this->hasMany(Question::class);
    }

    public function schools()
    {

        return $this->belongsToMany(School::class, 'grades_schools', 'grade_id', 'school_id');
    }

    public function users()
    {

        return $this->hasMany(User::class);
    }
}
