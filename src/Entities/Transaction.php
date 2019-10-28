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

class Transaction extends Model
{
    protected $table="menta_transactions";

    protected $fillable = [
        "order_id",
        "from_account_id",
        "to_account_id",
        "amount",
        "cashout",
        "revoked",
        "reverse",
    ];

    public function orders()
    {
        return $this->belongsTo(Order::class);
    }
}
