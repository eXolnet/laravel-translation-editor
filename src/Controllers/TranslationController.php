<?php namespace Exolnet\Translation\Editor\Controllers;

use App;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TranslationController extends Controller
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $locale = $request->get('locale');
        $path   = $request->get('path');

        App::setLocale(config('app.fallback_locale'));
        $source = __($path);

        App::setLocale($locale);
        $translation = __($path);

        return response()->json([
            'source'      => $source,
            'translation' => $translation,
        ]);
    }

    /**
     * @return void
     */
    public function store()
    {
        //
    }
}
