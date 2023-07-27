<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        // 'email',
        'phone_number',
        'isVerified',
        'dob',
        'password',
        'google_id',
        'avatar',
        'gender',
        'region'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function getGenderOptions(){
        return [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other'
        ];
    }

    public static function getRegionValues(){
        return [
            'kachin state' => 'Kachin State',
            'kayin state' => 'Kayin State',
            'kayah state' => 'Kayah State',
            'chin state' => 'Chin State',
            'mon state' => 'Mon State',
            'rakhine state' => 'Rakhine State',
            'shan state' => 'Shan State',
            'ayeyarwady division' => 'Ayeyarwady Division',
            'mandalay division' => 'Mandalay Division',
            'bago division' => 'Bago Division',
            'magway division' => 'Magway Division',
            'sagaing division' => 'Sagaing Division',
            'thanintharyi division' => 'Thanintharyi Division',
            'yangon division' => 'Yangon Division'
        ];
    }
}
