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

class Wallet extends Model
{
    protected $table="wallets";

    protected $fillable = [
        "title",
        "type",
        "revoked",
    ];


    public function accountTypes()
    {
        return $this->hasMany(AccountType::class, "wallet_id");
    }

}
