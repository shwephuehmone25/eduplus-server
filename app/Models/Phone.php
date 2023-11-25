<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Phone extends Model
{
    use HasFactory, Notifiable;

    public const PHONE_STATUS = [
        'invalidate' => 'INVALIDATE',
        'verify'     => 'VERIFY'
    ];

    protected $fillable = ['phone_number', 'phone_status'];

    public function routeNotificationForSmspoh()
    {

        return $this->phone_number;
    }

    public function otps()
    {

        return $this->hasMany(Otp::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
