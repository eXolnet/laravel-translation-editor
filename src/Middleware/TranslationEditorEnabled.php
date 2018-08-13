<?php

namespace Exolnet\Translation\Editor\Middleware;

use Closure;
use Exolnet\Translation\Editor\TranslationEditor;

class TranslationEditorEnabled
{
    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor
     */
    protected $translationEditor;

    /**
     * @param \Exolnet\Translation\Editor\TranslationEditor $translationEditor
     */
    public function __construct(TranslationEditor $translationEditor)
    {
        $this->translationEditor = $translationEditor;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->translationEditor->isEnabled()) {
            abort(404);
        }

        return $next($request);
    }
}
