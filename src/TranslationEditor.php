<?php namespace Exolnet\Translation\Editor;


use Illuminate\Translation\Translator;

class TranslationEditor
{
    /**
     * @var \Illuminate\Contracts\Translation\Translator|\Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * @param \Illuminate\Translation\Translator $translator
     */
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function detectLocales()
    {
        if ($locales = config('app.supported_locales')) {
            return $locales;
        }

        return [config('app.locale')];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return config('translation-editor.enabled');
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
            return $this->translator->getFromJson($key, $replace, $locale);
        }

        return $this->getEditor($key, $replace, $locale);
    }

    /**
     * @param string $key
     * @param array $replace
     * @param string|null $locale
     * @return string
     */
    public function getEditor($key, array $replace = [], $locale = null)
    {
        $translation = $this->translator->getFromJson($key, $replace, $locale);

        return '<translation-editor locale="'. ($locale ?: app()->getLocale()) .'" path="'. $key .'">'. $translation .'</translation-editor>';
    }
}
