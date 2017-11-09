<?php

if (! function_exists('te')) {
    function te($key, array $replace = [], $locale = null)
    {
        return app('translation.editor')->get($key, $replace, $locale);
    }
}
