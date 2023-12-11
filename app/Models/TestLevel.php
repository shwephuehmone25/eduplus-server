<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Result;
use Illuminate\Database\Eloquent\Model;

class TestLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grade_id',
        'school_id',
        'is_greater'
    ];

    public function result()
    {

        return $this->hasMany(Result::class);
    }
}
