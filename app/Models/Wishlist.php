<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'allocation_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allocation()
    {
        return $this->belongsTo(Allocation::class);
    }
}
