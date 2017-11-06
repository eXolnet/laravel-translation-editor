<?php namespace Exolnet\Translation\Editor\Controllers;

use App;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Translation\Translator;

class TranslationController extends Controller
{
    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Illuminate\Translation\Translator
     */
    protected $translator;

    /**
     * @param \Illuminate\Filesystem\Filesystem $filesystem
     * @param \Illuminate\Translation\Translator $translator
     */
    public function __construct(Filesystem $filesystem, Translator $translator)
    {
        $this->filesystem = $filesystem;
        $this->translator = $translator;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $locale = $request->get('locale');
        $path   = $request->get('path');

        // Get source translation
        $sourceLocale = $this->getSourceLocale($locale);
        $sourceValue  = $sourceLocale && $this->translator->has($path, $sourceLocale) ? $this->translator->get($path, [], $sourceLocale) : null;

        // Get actual translation
        $translation = $this->translator->has($path, $locale) ? $this->translator->get($path, [], $locale) : null;

        return response()->json([
            'path' => $path,
            'source' => [
                'locale'      => $sourceLocale,
                'translation' => $sourceValue,
            ],
            'destination' => [
                'locale' => $locale,
                'translation' => $translation,
            ],
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        $locale      = $request->get('locale');
        $path        = $request->get('path');
        $translation = $request->get('translation');

        list($namespace, $key) = explode('.', $path, 2);

        $locales = $this->translator->get($namespace, [], $locale);

        array_set($locales, $key, $translation);

        $filename = resource_path('lang/'. $locale .'/'. $namespace .'.php');
        $content  = '<?php'. PHP_EOL . PHP_EOL .'return '. $this->export($locales) .';'. PHP_EOL;

        $this->filesystem->put($filename, $content);
    }

    /**
     * @return array
     */
    protected function getLocales()
    {
        $availableLocales  = [$this->translator->getFallback()];
        $localeDirectories = $this->filesystem->directories(resource_path('lang'));

        foreach ($localeDirectories as $localeDirectory) {
            $availableLocales[] = pathinfo($localeDirectory, PATHINFO_FILENAME);
        }

        return array_unique($availableLocales);
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
                return var_export($expression, TRUE);
        }
    }
}
