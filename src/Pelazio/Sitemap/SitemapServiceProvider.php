<?php

namespace Pelazio\Sitemap;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Routing\ResponseFactory;

class SitemapServiceProvider extends ServiceProvider implements DeferrableProvider
{

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../views', 'sitemap');

        $config_file = __DIR__ . '/../../config/config.php';

        $this->mergeConfigFrom($config_file, 'sitemap');

        $this->publishes([
            $config_file => config_path('sitemap.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../../views' => base_path('resources/views/vendor/sitemap'),
        ], 'views');

        $this->publishes([
            __DIR__ . '/../../public' => public_path('vendor/sitemap'),
        ], 'public');
    }


    public function register(): void
    {
        $this->app->bind('sitemap', function (Container $app) {
            $config = $app->make('config');

            return new Sitemap(
                $config->get('sitemap'),
                $app->make('cache.store'),
                $config,
                $app->make('files'),
                $app->make(ResponseFactory::class),
                $app->make('view')
            );
        });

        $this->app->alias('sitemap', Sitemap::class);
    }

    public function provides(): array
    {
        return ['sitemap', Sitemap::class];
    }
}
