<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Question;

class Option extends Model
{
    use HasFactory;

    protected $fillable = ['question_id', 'option_text', 'points', 'status'];

    public function question()
    {

        return $this->belongsTo(Question::class);
    }

    public function getPointsAttribute($value)
    {
        $allowedRoles = ['super_admin', 'normal_admin'];

        if (auth()->check() && in_array(auth()->user()->role, $allowedRoles)) 
        {
            return $value;
        } else 
        {
            return null;
        }
    }

    public function getStatusAttribute($value)
    {
        $allowedRoles = ['super_admin', 'normal_admin'];

        if (auth()->check() && in_array(auth()->user()->role, $allowedRoles)) 
        {
            return $value;
        } else 
        {
            return null;
        }
    }
}
