<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\HtmlSanitizer;

class HtmlSanitizerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(HtmlSanitizer::class, function ($app) {
            $settings = config('htmlpurifier.settings', []);

            if (class_exists('\HTMLPurifier_Config')) {
                $config = \HTMLPurifier_Config::createDefault();
                foreach ($settings as $key => $value) {
                    $config->set($key, $value);
                }

                if (config('htmlpurifier.cache.enabled', false)) {
                    $config->set('Cache.SerializerPath', config('htmlpurifier.cache.path'));
                }

                return new HtmlSanitizer();
            }

            // Return instance that uses fallback behavior
            return new HtmlSanitizer();
        });
    }

    public function boot()
    {
        // nothing for now
    }
}
