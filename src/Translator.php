<?php

namespace Exolnet\Translation\Editor;

use Illuminate\Translation\Translator as LaravelTranslator;

class Translator extends LaravelTranslator
{
    /**
     * @return void
     */
    public function unloadAll()
    {
        $this->loaded = [];
    }

    /**
     * @param string $locale
     * @param string $namespace
     * @return array
     */
    public function getAllVariables($locale, $namespace = '*')
    {
        return array_dot(collect($this->loaded[$namespace])
            ->flatMap(function (array $locales, $group) use ($locale) {
                return [$group => $locales[$locale]];
            })
            ->all());
    }

    /**
     * @param string $locale
     * @param string $namespace
     * @return array
     */
    public function getAllDefinedNames($locale, $namespace = '*')
    {
        return collect($this->getAllVariables($locale, $namespace))
            ->keys()
            ->flatMap(function ($variable) {
                $splitted = explode('.', $variable);
                $newVar = '';
                $variables = [];

                foreach ($splitted as $item) {
                    $newVar .= $item . '.';
                    $variables[] = $newVar;
                }

                $index = \count($variables) - 1;
                $variables[$index] = substr($variables[$index], 0, -1);

                return $variables;
            })
            ->unique()
            ->sort()
            ->all();
    }
}
