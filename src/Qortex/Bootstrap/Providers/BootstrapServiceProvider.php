<?php

namespace Qortex\Bootstrap\Providers;

use Illuminate\Support\ServiceProvider;

use Form;
use Carbon\Carbon;

class BootstrapServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->loadViewsFrom(__DIR__ . '/resources/', 'qortex');
	}
}
