<?php

namespace Exolnet\Translation\Editor\Console;

use Exolnet\Translation\Editor\Exceptions\TranslationEditorException;
use Exolnet\Translation\Editor\TranslationEditor;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class TranslateCommand extends Command
{
    /**
     * @var array
     */
    protected const DETECTION_REGEXES = [
        '/@te\(\'(?P<variable>[a-z0-9\.-]+)\'[,)]/',
        '/\W__\(\'(?P<variable>[a-z0-9\.-]+)\'[,)]/',
        '/@lang\(\'(?P<variable>[a-z0-9\.-]+)\'[,)]/',
        '/\Wte\(\'(?P<variable>[a-z0-9\.-]+)\'[,)]/',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'i18n:translate {--r|review} {--f|filter=} {--t|target=*} {locale*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pass each translation variables to translate them';

    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor
     */
    protected $translationEditor;

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * @param \Exolnet\Translation\Editor\TranslationEditor $translationEditor
     */
    public function __construct(TranslationEditor $translationEditor)
    {
        parent::__construct();

        $this->translationEditor = $translationEditor;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->detectVariables();

        if (! $this->variables) {
            throw new TranslationEditorException('No variables found to be translated');
        }

        $locales = $this->argument('locale');

        foreach ($locales as $locale) {
            $this->translateLocale($locale);
        }
    }

    /**
     * @param string $locale
     */
    protected function translateLocale($locale)
    {
        $this->info('Translating locale ' . $locale . '...');

        $this->translationEditor->loadAllGroups($locale);

        $existingVariables = $this->translationEditor->getAllDefinedNames($locale);

        foreach ($this->variables as $variable) {
            if (in_array($variable, $existingVariables)) {
                continue;
            }

            $translation = $this->ask($variable, 'skip');

            if ($translation === 'skip') {
                continue;
            }

            $this->translationEditor->storeTranslation($variable, $translation, $locale);
        }
    }

    /**
     * @return void
     */
    protected function detectVariables()
    {
        $targets = $this->getTargets();
        $finder  = new Finder();

        $finder->files()->in($targets)->name('*.php');

        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            $content = $file->getContents();

            $this->detectVariableInContent($content);
        }

        $this->variables = array_unique($this->variables);
        sort($this->variables);
    }

    /**
     * @param string $content
     */
    protected function detectVariableInContent($content)
    {
        foreach (static::DETECTION_REGEXES as $regex) {
            if (! preg_match_all($regex, $content, $matches)) {
                continue;
            }

            $this->variables = array_merge($this->variables, $matches['variable']);
        }
    }

    /**
     * @return array
     */
    protected function getTargets()
    {
        if ($targets = (array)$this->option('target')) {
            return $targets;
        }

        return [
            base_path('app'),
            base_path('resources/views'),
        ];
    }
}
