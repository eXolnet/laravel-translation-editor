<?php

namespace Exolnet\Translation\Editor\Middleware;

use Closure;
use Exolnet\Translation\Editor\TranslationEditor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TranslationEditorInject
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
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldInjectTranslationEditor($response)) {
            $this->injectTranslationEditor($response);
        }

        return $response;
    }

    protected function injectTranslationEditor(Response $response)
    {
        $content      = $response->getContent();
        $headPosition = strpos($content, '</head>');


        $translationEditorHtml =
            '<script src="'. route('translation-editor.assets.js') .'" async></script>'.
            '<link href="'. route('translation-editor.assets.css') .'" rel="stylesheet" type="text/css">';

        // Build the content with our stats collector
        $content = substr($content, 0, $headPosition) . $translationEditorHtml . substr($content, $headPosition);
        $response->setContent($content);
    }

    /**
     * @param $response
     * @return bool
     */
    protected function shouldInjectTranslationEditor(Response $response)
    {
        return $this->translationEditor->isEnabled()
            && $response->headers->has('Content-Type')
            && strpos($response->headers->get('Content-Type'), 'html') !== false
            && strpos($response->getContent(), '</translation-editor>') !== false
            && strpos($response->getContent(), '</head>') !== false;
    }
}
