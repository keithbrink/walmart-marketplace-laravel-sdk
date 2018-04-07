# Walmart Marketplace API - Laravel Connector

This package connects to the [Walmart](https://developer.walmart.com) Marketplace API, allowing you to work with the API within Laravel. 

## Installation

This version supports Laravel 5.5. It may work with other versions but has not yet been tested.

To get the latest version, simply require the project using [Composer](https://getcomposer.org):

```bash
$ composer require keithbrink/walmart-marketplace-laravel-sdk
```

On Laravel 5.5, the `KeithBrink\Walmart\WalmartServiceProvider` service provider and `KeithBrink\Walmart\WalmartFacade` facade will be automatically discovered so it will not need to be added to your config. On previous versions (untested), you will need to add those manually to your `config/app.php`.

## Configuration

To get started, you should set your login information in the .env file:

```bash
WALMART_PRIVATE_KEY=XXX
WALMART_CONSUMER_ID=XXX
WALMART_BASE_URL=XXX
```

If you would like to change your env keys or set the keys in the config file, you can publish the config file:

```bash
$ php artisan vendor:publish
```

This will create a `config/walmart.php` file in your app, where you can adjust how the login information is found.

## Usage

After you have completed the configuration, you can call the various API functions. For example, to get the statuses of all feeds, you can call:

```bash
WalmartMkt::feedStatus()->all();
```

All of the functions are documented in the [Github Wiki](https://github.com/CrossroadsHuntley/dasco-laravel-sdk/wiki).

## License

The Walmart Marketplace Laravel SDK is licensed under [The MIT License (MIT)](LICENSE).
