<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'otp', 'expired_at'];

    public function user()
    {
        
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'expired_at' => 'datetime',
    ];
}
