<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Enums\OrderStatus;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'total',
        'reference',
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
     * The items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * The histories.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function histories()
    {
        return $this->hasMany(OrderHistory::class);
    }

    /**
     * The fees.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    /**
     * Store order request.
     * 
     * @param array $request
     * @param int $user_id
     * @return \App\Models\Order
     */
    public static function store(array $request, int $user_id): Order
    {
        return self::create([
            'user_id' => $user_id,
            'name' => $request['name'],
            'email' => $request['email'],
            'phone' => $request['phone'] ?? '',
            'total' => $request['total'],
        ]);
    }

    /**
     * Check if user has already bought a course.
     * 
     * @param int $course_id
     * @param int $user_id
     * @return bool
     */
    public static function hasBought(int $course_id, int $user_id): bool {
        $count = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('order_histories', 'orders.id', '=', 'order_histories.order_id')
            ->where('orders.user_id', $user_id)
            ->where('order_items.course_id', $course_id)
            ->where('order_histories.status', OrderStatus::STATUS['SUCCEED'])
            ->count();
        return $count > 0; 
    }
}
