<?php

namespace App\Models;

use App\Models\User;
use App\Models\Course;
use App\Models\Meeting;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Authenticatable as AuthenticableTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
        'access_token',
        'refresh_token',
    ];

    public function courses()
    {

        return $this->belongsToMany(Course::class, 'teacher_courses', 'teacher_id','course_id');
    }

    public function meeting()
    {

        return $this->hasOne(Meeting::class);
    }

    public function students()
    {

        return $this->belongsToMany(User::class, 'teachers_students', 'teacher_id','user_id');
    }

    public function sections()
    {

        return $this->belongsToMany(Section::class, 'teachers_sections', 'teacher_id','section_id')
                    ->withPivot('course_id');
    }

    public function allocations()
    {

        return $this->hasMany(Allocation::class);
    }
}
