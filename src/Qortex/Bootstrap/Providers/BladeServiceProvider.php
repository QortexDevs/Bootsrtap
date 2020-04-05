<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::directive('money', function ($expression) {
            return "<?php echo number_format($expression, 2, ',', ' '); ?>";
        });
        
        Blade::directive('periodNameRu', function ($expression) {
            return "<?php echo number_format($expression, 2, ',', ' '); ?>";
        });
    }
}