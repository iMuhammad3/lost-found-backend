<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    //
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'location',
        'status',
        'recovery_status',
        'image',
    ];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return asset('images/placeholder.png');
    }
    // An item belongs to a user
    public function user() {
        return $this->belongsTo(User::class);
    }
}
