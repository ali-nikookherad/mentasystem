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

set this repository into your OS.

```sh
$ composer config repositories.private-packagist
$ composer https://repo.packagist.com/mentasystem/
$ composer config repositories.packagist.org false

```
### Installation
```sh
$ composer require mentasystem/wallet
$ php artisan migrate
```

##### Add this class provider into your config/app.php file

```sh
$ \Mentasystem\Wallet\WalletServiceProvider::class
```
### Development

Want to be a contributer? Great!

