<?php

namespace App\Models;

use App\Models\Phone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = ['phone_id', 'otp', 'expired_at'];

    public function phone()
    {
        
        return $this->belongsTo(Phone::class);
    }

    protected $casts = [
        'expired_at' => 'datetime',
    ];
}
