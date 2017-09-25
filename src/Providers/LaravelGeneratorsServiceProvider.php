<?php declare(strict_types=1);

namespace Simlux\LaravelGenerators;

use Illuminate\Support\ServiceProvider;
use Simlux\LaravelGenerators\Console\Commands\Generator\ModelGeneratorCommand;

class LaravelGeneratorsServiceProvider extends ServiceProvider
{
    private const CONFIG_FILE = __DIR__ . '/../../config/laravel_generators.php';

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            self::CONFIG_FILE => config_path('laravel_generators.php'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ModelGeneratorCommand::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            self::CONFIG_FILE, 'laravel_generators'
        );
    }
}
