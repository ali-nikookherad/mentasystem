<?php

namespace Mentasystem\Wallet\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Class Account
 * @package Mentasystem\Wallet\Entities
 */
class Account extends Model
{
    protected $table = 'menta_accounts';

    protected $fillable = [
        "account_type_id",
        "treasury_id",
        "user_id",
        "revoked",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function credits()
    {
        return $this->hasMany(Credit::class, "account_id");
    }

    public function accountType()
    {
        return $this->belongsTo(AccountType::class);
    }

    public function account()
    {
        return $this->hasOne(Account::class, "treasury_id", "id");
    }

}
