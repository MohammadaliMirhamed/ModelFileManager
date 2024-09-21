<?php

namespace Roui\ModelFileManager;

use Illuminate\Support\ServiceProvider;
use Roui\ModelFileManager\Console\Commands\ModelFileManager;

class FileStorageServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * This function publishes the package's configuration file
     * to the application's /config folder when the package is installed.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish the config file to the application's config directory
        $this->publishes([
            __DIR__ . '/config/modelfilemanager.php' => config_path('modelfilemanager.php'),
        ], 'config');
    }

    /**
     * Register any package services and bindings.
     *
     * This function merges the package's default configuration file with the application's config,
     * and registers the custom Artisan command provided by the package.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge the default configuration file
        $this->mergeConfigFrom(
            __DIR__ . '/config/modelfilemanager.php', 'modelfilemanager'
        );

        // Register custom Artisan commands
        $this->commands([
            ModelFileManager::class,
        ]);
    }
}

