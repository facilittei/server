<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'photo',
        'bio',
        'lang',
    ];

    /**
     * The user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the photo image URL.
     *
     * @return string
     */
    public function getPhotoAttribute()
    {
        if ($this->attributes['photo']) {
            return config('app.assets_url') .'/'. $this->attributes['photo'];
        }
    }
}
