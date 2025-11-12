<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
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
        // Register view composers
        \Illuminate\Support\Facades\View::composer(
            ['layouts.app', 'layouts.admin', 'front.*'],
            \App\View\Composers\SettingsComposer::class
        );
    }
}
