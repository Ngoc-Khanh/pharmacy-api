<?php

namespace App\Providers;

use App\Utils\SwaggerSecurityFilter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Add our SwaggerSecurityFilter to the l5-swagger defaults config
        $this->app->resolving('L5Swagger\Generator', function ($generator, $app) {
            $docs = config('l5-swagger.documentations', []);
            foreach (array_keys($docs) as $name) {
                config(["l5-swagger.documentations.{$name}.paths.swagger_filters" => [
                    SwaggerSecurityFilter::class,
                ]]);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Đảm bảo route cho Swagger được tải
    }
}
