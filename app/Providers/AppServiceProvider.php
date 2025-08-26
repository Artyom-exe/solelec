<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HtmlSanitizer;
use Illuminate\Filesystem\Filesystem;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind HtmlSanitizer singleton using config/htmlpurifier when available.
        $this->app->singleton(HtmlSanitizer::class, function ($app) {
            $config = $app['config']->get('htmlpurifier', []);

            $cachePath = $config['cache_path'] ?? storage_path('app/htmlpurifier');
            if (! is_dir($cachePath)) {
                (new Filesystem)->ensureDirectoryExists($cachePath, 0755, true);
            }

            // If HTMLPurifier exists (vendor), instantiate and pass to service.
            if (class_exists('\\HTMLPurifier')) {
                try {
                    $cfg = \HTMLPurifier_Config::createDefault();
                    if (! empty($config['settings']) && is_array($config['settings'])) {
                        foreach ($config['settings'] as $key => $value) {
                            $cfg->set($key, $value);
                        }
                    }
                    $cfg->set('Cache.SerializerPath', $cachePath);
                    $purifier = new \HTMLPurifier($cfg);
                    return new HtmlSanitizer($purifier);
                } catch (\Throwable $e) {
                    // Fall back to non-purifier sanitizer
                    return new HtmlSanitizer();
                }
            }

            return new HtmlSanitizer();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
