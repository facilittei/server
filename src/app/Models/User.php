<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'role',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return $this->email_verified_at;
    }

    /**
     * The courses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    /**
     * The enrolled courses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function enrolled()
    {
        return $this->belongsToMany(Course::class)->where('is_published', true);
    }

    /**
     * The watched.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function watched()
    {
        return $this->belongsToMany(Lesson::class)->withTimestamps();
    }

    /**
     * The favorited.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function favorited()
    {
        return $this->belongsToMany(Lesson::class, 'favorite_lesson')->withTimestamps();
    }

    /**
     * The comments.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * User's groups.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    /**
     * The profile.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * The addresses.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * The orders.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * The Payment Service Providers (PSP).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentPlatforms()
    {
        return $this->hasMany(PaymentPlatform::class);
    }

    /**
     * Send user password reset notification link.
     *
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token, $this));
    }

    /**
     * Courses by status (published)
     *
     * @param  bool  $is_published
     * @param  int  $limit
     * @return array<Course>
     */
    public function getCourseByStatus($is_published, $limit)
    {
        $courses = $this->courses()->select(
            'id',
            'title',
            'slug',
            'is_published',
            'cover',
            'created_at',
            'updated_at'
        )->where('is_published', $is_published)->limit($limit)->get();

        if (count($courses)) {
            return $courses;
        }

        return [];
    }
}
