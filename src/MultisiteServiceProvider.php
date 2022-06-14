<?php

namespace MediaBoutique\Multisite;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use MediaBoutique\Multisite\Facades\Multisite;

class MultisiteServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            'multisite',
            \MediaBoutique\Multisite\Multisite::class,
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!app()->runningInConsole()) {

            $host = request()->getHost();

            if (!in_array($host, config('multisite.exclude_hosts', []))) {

                Multisite::init($host);

                View::addLocation(resource_path('sites/' . Multisite::alias() . '/views'));

                View::addNamespace(Multisite::alias(), resource_path('sites/' . Multisite::alias() . '/views'));
            }
        } else {

            $this->publishes([
                __DIR__ . '/../config/multisite.php' => config_path('multisite.php'),
            ], 'config');

            $this->commands([
                \MediaBoutique\Multisite\Console\Setup::class,
                \MediaBoutique\Multisite\Console\Site::class,
            ]);
        }
    }
}
