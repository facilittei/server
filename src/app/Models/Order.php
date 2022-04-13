<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

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
        'document',
        'address_street',
        'address_number',
        'address_complement',
        'address_city',
        'address_state',
        'address_postcode',
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
     * Store order request.
     * 
     * @param array $request
     * @param int $user_id
     * @return App\Models\Order
     */
    public static function store(array $request, int $user_id): Order
    {
        return self::create([
            'user_id' => $user_id,
            'name' => $request['customer']['name'],
            'email' => $request['customer']['email'],
            'phone' => $request['customer']['phone'] ?? '',
            'document' => $request['customer']['document'],
            'address_street' => $request['customer']['address']['street'],
            'address_number' => $request['customer']['address']['number'],
            'address_complement' => $request['customer']['address']['complement'] ?? '',
            'address_city' => $request['customer']['address']['city'],
            'address_state' => $request['customer']['address']['state'],
            'address_postcode' => $request['customer']['address']['post_code'],
            'total' => $request['total'],
        ]);
    }
}
