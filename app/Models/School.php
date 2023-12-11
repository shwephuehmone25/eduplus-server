<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Grade;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use App\Models\Question;

class School extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function grade()
    {

        return $this->hasOne(Grade::class);
    }

    public function users()
    {

        return $this->hasMany(User::class);
    }

    public function questions()
    {

        return $this->hasMany(Question::class);
    }
}
