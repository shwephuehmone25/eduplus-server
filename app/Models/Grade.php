<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Question;
use App\Models\School;
use App\Models\User;
use App\Models\TestLevel;

class Grade extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'school_id'];

    public function questions()
    {

        return $this->hasMany(Question::class);
    }

    public function school()
    {

        return $this->belongsTo(School::class);
    }

    public function users()
    {

        return $this->hasMany(User::class);
    }

    public function testLevels()
    {
        return $this->hasMany(TestLevel::class);
    }
}
