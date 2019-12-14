# Wallet


[![Build Status](https://travis-ci.org/joemccann/dillinger.svg?branch=master)](https://github.com/ali-nikookherad/mentasystem.git)

wallet first version (alpha release) .

# version 1.0.0 !

  - create system wallet types
  - create your system account types
  - create your account (automaically assign to your account type)
  - charge your account
  - submit your order

You can also:
  - get report from your data such as:
    - get transactions and filter it
    - get orders and filter it


### Requirement

wallet requires [laravel framework](https://laravel.com/) v5.8 to run.


### Installation
```sh
$ composer require mentasystem/wallet
```

##### Add this class provider into your config/app.php file

```sh
$ \Mentasystem\Wallet\WalletServiceProvider::class
```

### run
```sh
$ php artisan migrate
```


### Development

Want to be a contributer? Great!

