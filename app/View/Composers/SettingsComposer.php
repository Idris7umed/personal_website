<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;

class SettingsComposer
{
    /**
     * Bind data to the view.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        // Cache settings for 1 hour to reduce database queries
        $settings = Cache::remember('site_settings', 3600, function () {
            return User::first();
        });

        $view->with('settings', $settings);
    }
}
