<?php

namespace App\Models;

use App\Models\Notice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variety extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function news()
    {

        return $this->hasMany(Notice::class);
    }
}
