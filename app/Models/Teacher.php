<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;

class Teacher extends Model implements Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, AuthenticableTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at', 
        'password',
        'google_id',
        'avatar',
        'role',
    ];

    public function courses(){
        
        return $this->belongsToMany(Course::class, 'teacher_courses', 'course_id','teacher_id');
    }
}
