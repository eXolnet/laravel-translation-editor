<?php

namespace Exolnet\Translation\Editor;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Translation\Translator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class TranslationEditor
{
    /**
     * @var \Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * @var \Illuminate\Filesystem\Filesystem;
     */
    protected $files;

    /**
     * TranslationEditor constructor.
     *
     * @param \Illuminate\Contracts\Config\Repository $config
     * @param \Illuminate\Translation\Translator $translator
     * @param \Illuminate\Filesystem\Filesystem; $files
     */
    public function __construct(Config $config, Translator $translator, Filesystem $files)
    {
        $this->config = $config;
        $this->translator = $translator;
        $this->files = $files;
    }

    /**
     * @return array
     */
    public function detectLocales()
    {
        if ($locales = $this->config->get('app.supported_locales')) {
            return $locales;
        }

        return [$this->config->get('app.locale')];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->config->get('translation-editor.enabled');
    }

    /**
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public function get($key, array $replace = [], $locale = null)
    {
        if (! $this->isEnabled()) {
            return $this->getTranslation($key, $replace, $locale);
        }

        return $this->getEditor($key, $replace, $locale);
    }

    /**
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public function getTranslation($key, array $replace = [], $locale = null)
    {
        // Before Laravel 5.8, method getFromJson should be used
        if (method_exists($this->translator, 'getFromJson')) {
            return $this->translator->getFromJson($key, $replace, $locale);
        }

        return $this->translator->get($key, $replace, $locale);
    }

    /**
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public function getEditor($key, array $replace = [], $locale = null)
    {
        $translation = $this->getTranslation($key, $replace, $locale);

        return '<translation-editor locale="'. ($locale ?: $this->config->get('app.locale')) .'" path="'. $key .'">'.
                $translation .
            '</translation-editor>';
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        $availableLocales  = [$this->translator->getFallback()];
        $localeDirectories = $this->files->directories(resource_path('lang'));

        foreach ($localeDirectories as $localeDirectory) {
            $availableLocales[] = pathinfo($localeDirectory, PATHINFO_FILENAME);
        }

        return array_unique($availableLocales);
    }

    /**
     * @param string $path
     * @param string|null $locale
     * @return array
     */
    public function retrieveTranslation($path, $locale = null)
    {
        // Get source translation
        $sourceLocale = $this->getSourceLocale($locale);

        $sourceValue = $sourceLocale && $this->translator->has($path, $sourceLocale)
            ? $this->translator->get($path, [], $sourceLocale)
            : null;

        // Get actual translation
        $translation = $this->translator->has($path, $locale) ? $this->translator->get($path, [], $locale) : null;

        return [
            'path' => $path,
            'source' => [
                'locale'      => $sourceLocale,
                'translation' => $sourceValue,
            ],
            'destination' => [
                'locale' => $locale,
                'translation' => $translation,
            ],
        ];
    }

    /**
     * @param string $path
     * @param string $translation
     * @param string|null $locale
     */
    public function storeTranslation($path, $translation, $locale = null)
    {
        list($namespace, $key) = explode('.', $path, 2);

        $locales = $this->translator->has($namespace, $locale) ? $this->translator->get($namespace, [], $locale) : [];

        Arr::set($locales, $key, $translation);

        $filename = resource_path('lang/'. $locale .'/'. $namespace .'.php');
        $content  = '<?php'. PHP_EOL . PHP_EOL .'return '. $this->export($locales) .';'. PHP_EOL;

        $this->files->put($filename, $content);

        //$this->translator->unloadAll();
    }

    /**
     * @param string $forLocale
     * @return string|null
     */
    protected function getSourceLocale($forLocale)
    {
        foreach ($this->getLocales() as $locale) {
            if ($forLocale === $locale) {
                continue;
            }

            return $locale;
        }

        return null;
    }

    /**
     * @param mixed $expression
     * @param string $indent
     * @return string
     */
    protected function export($expression, $indent = '')
    {
        switch (gettype($expression)) {
            case 'array':
                $isIndexed = array_keys($expression) === range(0, count($expression) - 1);
                $result  = [];

                foreach ($expression as $key => $value) {
                    $line = $indent .'    ';

                    if (! $isIndexed) {
                        $line .= $this->export($key) . ' => ';
                    }

                    $line .= $this->export($value, $indent .'    ');

                    $result[] = $line;
                }

                return '[' . PHP_EOL . implode(','.PHP_EOL, $result) . PHP_EOL . $indent . ']';

            default:
                return var_export($expression, true);
        }
    }

    /**
     * @param string $locale
     * @return array
     */
    public function getGroups($locale)
    {
        $finder     = new Finder();
        $localePath = resource_path('lang/' . $locale);

        $finder->files()->in($localePath)->name('*.php')->depth(0);

        return collect()
            ->concat($finder)
            ->map(function (SplFileInfo $file) {
                return $file->getBasename('.php');
            })
            ->all();
    }

    /**
     * @param string $locale
     */
    public function loadAllGroups($locale)
    {
        $groups = $this->getGroups($locale);

        foreach ($groups as $group) {
            $this->translator->load('*', $group, $locale);
        }
    }

    /**
     * @param string $text
     * @param string $locale
     * @return array
     */
    public function findVariablesForText($text, $locale)
    {
//        $this->loadAllGroups($locale);
//
//        return collect($this->translator->getAllVariables($locale))
//            ->filter(function($value) use ($text) {
//                return $value === $text;
//            })->keys()->all();

        return [];
    }

    /**
     * @param string $locale
     * @return array
     */
    public function getAllDefinedNames($locale)
    {
//        $this->loadAllGroups($locale);
//
//        return $this->translator->getAllDefinedNames($locale);

        return [];
    }
}
