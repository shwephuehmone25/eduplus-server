<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notice extends Model
{
    use HasFactory, SoftDeletes;

    public function variety()
    {

        return $this->belongsTo(Variety::class);
    }

    public function admin()
    {

        return $this->belongsTo(Admin::class);
    }

    public function images()
    {

        return $this->morphMany(Image::class, 'imageable');
    }
}
