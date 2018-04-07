<?php

namespace KeithBrink\Walmart;

use Illuminate\Support\ServiceProvider;

class WalmartServiceProvider extends ServiceProvider
{
	/**
	 * Boot the Service Provider, add publishing for config
	 * @return null
	 */
	public function boot() {
		$this->publishes([
	        __DIR__.'/../config/walmart.php' => config_path('walmart.php'),
	    ]);
	}

	/**
	 * Register the service provider
	 * @return null
	 */
    public function register()
    {
    	$this->app->bind('walmart', function () {
            return new WalmartManager;
        });

        $this->mergeConfigFrom(
	        __DIR__.'/../config/walmart.php', 'walmart'
	    );
    }
}