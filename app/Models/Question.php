<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Option;
use App\Models\Type;
use App\Models\School;
use App\Models\TestLevel;
use App\Models\Collection;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['school_id', 'type_id', 'grade_id', 'collection_id', 'question_text'];

    public function school()
    {
     return $this->belongsTo(School::class);
    }

    public function grade()
    {
     return $this->belongsTo(Grade::class);
    }

    public function type()
    {
     return $this->belongsTo(Type::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }

    public function collections()
    {
        return $this->hasMany(Collection::class);
    }

    public function testLevel()
    {
        return $this->belongsTo(TestLevel::class);
    }
}
