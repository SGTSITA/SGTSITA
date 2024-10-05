<?php

namespace App\Providers;

use App\Models\Asignaciones;
use App\Models\BancoDinero;
use App\Models\BancoDineroOpe;
use App\Models\Bancos;
use App\Models\Configuracion;
use App\Models\Cotizaciones;
use App\Models\GastosGenerales;
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
        view()->composer('*', function ($view) {


            if( auth()->user() == null){
                $configuracion = Configuracion::first();

            }else{
                $configuracion = auth()->user()->Empresa->Configuracion;

            }

            $view->with(['configuracion' => $configuracion]);
        });
    }
}
