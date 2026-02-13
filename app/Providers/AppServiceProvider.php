<?php

namespace App\Providers;

use App\Models\Contacto;
use App\Models\Logos;
use App\Models\Provincia;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        View::composer('*', function ($view) {
            $view->with([
                'provincias' => Provincia::orderBy('name', 'asc')->with('localidades')->get(),

                'contacto' => Contacto::first(),
                'logos' => Logos::first()
            ]);
        });
        if (app()->environment('production')) {
            URL::forceScheme(scheme: 'https');
        }
    }
}
