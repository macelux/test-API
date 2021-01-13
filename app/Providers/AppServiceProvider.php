<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use App\Option;
use Illuminate\Support\Facades\App;

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
        // if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
        //     URL::forceScheme('https');
        // }

        // try {
        //     DB::connection()->getPdo();

        //     /**
        //      * Get settings and set it to config
        //      */
        //     $options = Option::all()->pluck('option_value', 'option_key')->toArray();
        //     $configs = [];
        //     $configs['options'] = $options;

        //     /**
        //      * Get language file
        //      */
        //     $configs['lang_str'] = [];
        //     $local = app()->getLocale();
        //     $language_path = App::langPath() . '/' . $local;


        //     /**
        //      * Get option in some specific way
        //      */
        //     $configs['options']['allowed_file_types_arr'] = array_filter(explode(',', array_get($options, 'allowed_file_types')));

        //     // if (file_exists($language_path)) {
        //     //     $configs['lang_str'] = include_once $language_path;
        //     // }

        //     $configs['lang_str'] = $language_path;

        //     $configs['app.timezone'] = array_get($options, 'default_timezone');
        //     $configs['app.url'] = array_get($options, 'site_url');
        //     $configs['app.name'] = array_get($options, 'site_title');

        //     //$configs = apply_filters('app_configs', $configs);
        //     config($configs);
        // } catch (\Exception $e) {
        //     //
        // }
    }
}
