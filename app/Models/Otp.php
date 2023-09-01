<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'otp', 'is_verified'];

    public function user()
    {
        
        return $this->belongsTo(User::class);
    }
}
