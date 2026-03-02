<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityRequirement;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->components->securitySchemes['api-key'] = SecurityScheme::apiKey('header', 'X-API-KEY');
                $openApi->components->securitySchemes['bearer'] = SecurityScheme::http('bearer');

                $openApi->security[] = new SecurityRequirement([
                    'api-key' => [],
                ]);

                $openApi->security[] = new SecurityRequirement([
                    'bearer' => [],
                ]);
            });

        // Allow access to Scramble docs in production/staging
        Gate::define('viewApiDocs', function ($user = null) {
            // Option 1: Allow everyone (only if you want public API docs)
            return true;

            // Option 2: Allow only specific users (Recommended)
            // return in_array($user->email['admin@example.com']);

            // Option 3: Allow only in non-production environments
            // return !App::environment('production');
        });
    }
}
