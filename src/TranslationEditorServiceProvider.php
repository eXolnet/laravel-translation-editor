<?php namespace Exolnet\Translation\Editor;

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
        app(Kernel::class)->pushMiddleware(TranslationEditorMiddleware::class);

        $this->registerBladeDirectives();
    }

    /**
     * @return void
     */
    protected function registerBladeDirectives()
    {
        Blade::directive('text', function ($expression) {
            $compiled = "<?php echo app('translator')->getFromJson({$expression}); ?>";

            if (! config('app.debug')) {
                return $compiled;
            }

            return "<translation-editor name={$expression}>{$compiled}</translation-editor>";
        });
    }
}
