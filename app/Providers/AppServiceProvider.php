<?php

namespace App\Providers;

use App\Utils\SwaggerSecurityFilter;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
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
        if($this->app->environment('production')) URL::forceScheme('https');

        // Tạo response macro cho CORS
        Response::macro('withCors', function () {
            /** @var \Illuminate\Http\Response $this */
            return $this->header('Access-Control-Allow-Origin', '*')
                        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                        ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        });
    }
}
