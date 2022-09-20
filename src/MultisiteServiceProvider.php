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
        $this->publishes([
            __DIR__ . '/../config/multisite.php' => config_path('multisite.php'),
        ], 'config');

        $this->commands([
            \MediaBoutique\Multisite\Console\Setup::class,
            \MediaBoutique\Multisite\Console\Site::class,
        ]);

        if (Multisite::installed()) {

            $host = request()->getHost();

            if (!in_array($host, config('multisite.exclude_hosts', []))) {

                Multisite::init($host);

                if (Multisite::active()) {

                    $path_views = resource_path('sites/' . Multisite::alias() . '/views');

                    View::addLocation($path_views);

                    View::addNamespace(Multisite::alias(), $path_views);
                }
            }
        }
    }
}
