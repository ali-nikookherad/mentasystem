<?php


namespace Mentasystem\Wallet\Facades;
use Illuminate\Support\Facades\Facade;

class Order extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Order';
    }
}
