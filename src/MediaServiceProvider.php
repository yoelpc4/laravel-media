<?php

namespace Yoelpc4\LaravelMedia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/media.php', 'media');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var Model $mediaModel */
        $mediaModel = $this->app->config['media.model'];

        $mediaObserver = $this->app->config['media.observer'];

        $mediaModel::observe($mediaObserver);

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'laravel-media');

        $this->publishes([
            __DIR__.'/../config/media.php' => config_path('media'),
        ], 'config');

        if (!class_exists('CreateMediasTable')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_medias_table.php.stub' => database_path('migrations'.date('Y_m_d_His', time()).'_create_medias_table.php'),
            ], 'migrations');
        }

        $this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/laravel-media'),
        ], 'resources');
    }
}
