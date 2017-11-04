<?php namespace Exolnet\Translation\Editor;

use Closure;
use Illuminate\Http\Request;

class TranslationEditorMiddleware
{
	/**
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 * @throws \Exception
	 */
	public function handle(Request $request, Closure $next)
	{
		return $next($request);
	}
}
