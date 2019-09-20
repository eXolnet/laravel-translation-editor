<?php

namespace Exolnet\Translation\Editor;

use Exolnet\Translation\Editor\Middleware\TranslationEditorEnabled;
use Exolnet\Translation\Editor\Middleware\TranslationEditorInject;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class TranslationEditorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
        $this->registerBladeDirectives();
        $this->setupRoutes();
        $this->setupMiddleware();
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TranslationEditor::class, function (Container $app) {
            return new TranslationEditor(
                $app['config'],
                $app['translator'],
                $app['files']
            );
        });

        $this->app->alias(TranslationEditor::class, 'translation.editor');

        $this->registerCommands();
    }

    /**
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../config/translation-editor.php');

        $this->publishes([
            $source => config_path('translation-editor.php')
        ]);

        $this->mergeConfigFrom($source, 'translation-editor');
    }

    /**
     * @return void
     */
    protected function registerBladeDirectives()
    {
        Blade::directive('te', function ($expression) {
            return "<?php echo app('translation.editor')->get({$expression}); ?>";
        });
    }

    /**
     * @return void
     */
    protected function setupRoutes()
    {
        $routeConfig = [
            'namespace' => 'Exolnet\\Translation\\Editor\\Controllers',
            'prefix' => '_translation-editor',
            'middleware' => [TranslationEditorEnabled::class],
        ];

        $this->app['router']->group($routeConfig, function ($router) {
            $router->get('translation', [
                'uses' => 'TranslationController@show',
                'as'   => 'translation-editor.translation.show',
            ]);

            $router->post('translation', [
                'uses' => 'TranslationController@store',
                'as'   => 'translation-editor.translation.store',
            ]);

            $router->get('assets/javascript', [
                'uses' => 'AssetController@js',
                'as'   => 'translation-editor.assets.js',
            ]);

            $router->get('assets/css', [
                'uses' => 'AssetController@css',
                'as'   => 'translation-editor.assets.css',
            ]);
        });
    }

    /**
     * @return void
     */
    protected function setupMiddleware()
    {
        app(Kernel::class)->pushMiddleware(TranslationEditorInject::class);
    }

    /**
     * @return void
     */
    protected function registerCommands()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\DetectCommand::class,
            Console\TranslateCommand::class,
        ]);
    }
}
