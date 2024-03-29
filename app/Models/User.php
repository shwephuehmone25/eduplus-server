<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Enrollment;
use App\Contracts\Likeable;
use App\Models\Like;
use App\Models\Section;
use App\Models\Allocation;
use App\Models\Wishlist;
use App\Models\Rank;
use App\Models\Grade;
use App\Models\School;
use App\Models\Result;
use App\Models\Phone;
use App\Models\Payment;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        // 'email',
        'phone_id',
        'dob',
        'password',
        'google_id',
        'avatar',
        'gender',
        'region',
        'address',
        'image_url',
        'expired_at',
    ];

    protected $dates = [
        'expired_at',
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

    public function phone()
    {
        return $this->belongsTo(Phone::class);
    }

    public function courses()
    {

        return $this->belongsToMany(Course::class, 'students_sections', 'user_id','course_id');
    }

    public function teachers()
    {

        return $this->belongsToMany(Teacher::class, 'teachers_students', 'user_id', 'teacher_id');
    }

    public function enrollments()
    {

        return $this->belongsToMany(Enrollment::class, 'students_enrollments', 'user_id', 'enrollment_id');
    }

    public function allocations()
    {

        return $this->belongsToMany(Allocation::class, 'students_allocations', 'user_id' , 'allocation_id');
    }

    public function grade()
    {

        return $this->belongsTo(Grade::class);
    }

    public function school()
    {

        return $this->belongsTo(School::class);
    }

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
            'tanintharyi division' => 'Tanintharyi Division',
            'yangon division' => 'Yangon Division'
        ];
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function like(Likeable $likeable): self
    {
        if ($this->hasLiked($likeable)) {
            return $this;
        }

        (new Like())
            ->user()->associate($this)
            ->likeable()->associate($likeable)
            ->save();

        return $this;
    }

    public function unlike(Likeable $likeable): self
    {
        if (! $this->hasLiked($likeable)) {
            return $this;
        }

        $likeable->likes()
            ->whereHas('user', fn($q) => $q->whereId($this->id))
            ->delete();

        return $this;
    }

    public function hasLiked(Likeable $likeable): bool
    {
        if (! $likeable->exists) {
            return false;
        }

        return $likeable->likes()
            ->whereHas('user', fn($q) =>  $q->whereId($this->id))
            ->exists();
    }

    public function sections()
    {

        return $this->belongsToMany(Section::class, 'students_sections', 'user_id' , 'section_id');
    }

    public function modules()
    {

        return $this->belongsToMany(Rank::class, 'students_modules', 'user_id', 'rank_id');
    }

    public function images()
    {

        return $this->morphMany(Image::class, 'imageable');
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
