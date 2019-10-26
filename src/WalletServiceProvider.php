<?php

namespace Mentasystem\Wallet;

use Illuminate\Support\ServiceProvider;
use Mentasystem\Wallet\Http\Controllers\Api\AccountController;
use Mentasystem\Wallet\Http\Controllers\Api\AccountTypeController;
use Mentasystem\Wallet\Http\Controllers\Api\OrderController;
use Mentasystem\Wallet\Http\Controllers\Api\WalletController;

class WalletServiceProvider extends ServiceProvider
{
    public function boot()
    {
//        include __DIR__ . '/Routes/api.php';
        $this->loadRoutesFrom(__DIR__.'/Routes/api.php');
        $this->loadMigrationsFrom(__DIR__.'/Database');
    }

    public function register()
    {
        //controller facade
        $this->app->bind('Account', function () {
            return new AccountController();
        });

        $this->app->bind('AccountType', function () {
            return new AccountTypeController();
        });

        $this->app->bind('Order', function () {
            return new OrderController();
        });

        $this->app->bind('Wallet', function () {
            return new WalletController();
        });

//        $this->publishes([__DIR__ . '/Database', database_path("migrations")], "migration");
    }
}
