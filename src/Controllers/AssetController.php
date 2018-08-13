<?php

namespace Exolnet\Translation\Editor\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class AssetController extends Controller
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function js()
    {
        $content = file_get_contents(__DIR__ .'/../../resources/js/translation-editor.js');

        $content = str_replace('{baseRoute}', url('_translation-editor'), $content);

        $response = new Response($content, 200, [
            'Content-Type' => 'text/javascript',
        ]);

        return $this->cacheResponse($response);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function css()
    {
        $content = file_get_contents(__DIR__ .'/../../resources/css/translation-editor.css');

        $response = new Response($content, 200, [
            'Content-Type' => 'text/css',
        ]);

        return $this->cacheResponse($response);
    }

    /**
     * Cache the response 1 year (31536000 sec)
     */
    protected function cacheResponse(Response $response)
    {
        $response->setSharedMaxAge(31536000);
        $response->setMaxAge(31536000);
        $response->setExpires(new \DateTime('+1 year'));

        return $response;
    }
}
