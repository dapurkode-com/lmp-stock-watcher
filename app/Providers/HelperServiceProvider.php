<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * HelperServiceProvider is a provider to load all
 * custom helper file on App\Helpers
 *
 * @package App\Providers
 * @author Satya Wibawa <i.g.b.n.satyawibawa@gmail.com>
 */
class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // load all helper
        foreach (glob(app_path().'/Helpers/*.php') as $filename){
            require_once($filename);
        }
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
