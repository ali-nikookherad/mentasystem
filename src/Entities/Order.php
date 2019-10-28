<?php

namespace Mentasystem\Wallet\Entities;

/**
 * Modules\Wallet\Entities
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Client[] $clients
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\Laravel\Passport\Token[] $tokens
 * @mixin \Eloquent
 */

use Illuminate\Database\Eloquent\Model;
use Mentasystem\Account\Entities\Credit;

class Order extends Model
{

    protected $table="menta_orders";

    protected $fillable = [
        "user_id",
        "goods_id",
        "type",
        "from_account_id",
        "to_account_id",
        "refund",
        "cashout",
        "paid_at",
        "amount",
        "treasury_account_id",
    ];


    public function goods()
    {
        return $this->belongsTo(Goods::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, "order_id");
    }

    public function credit()
    {
        return $this->belongsTo(Credit::class);
    }

    public function productOrder()
    {
        return $this->belongsTo(ProductOrder::class, "goods_id");
    }
}
