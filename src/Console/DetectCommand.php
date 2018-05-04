<?php namespace Exolnet\Translation\Editor\Console;

use Exolnet\Translation\Editor\TranslationEditor;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class DetectCommand extends Command
{
    /**
     * @var array
     */
    const DETECTION_REGEXES = [
        '/\s*(?P<line>.*\s(?P<context>(?:title|alt)="\s*(?P<text>[^"]+)\s*")[^\n]*)/',
        '/\s*(?P<line>.*(?P<context>>\s*(?P<text>[^<>\n]+?)\s*<\/)[^\n]*)/',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'i18n:detect {target?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect text without translation variables in views.';

    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor
     */
    protected $translationEditor;

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
    public function handle(TranslationEditor $translationEditor)
    {
        $targets = $this->getTargets();
        $finder  = new Finder();

        $finder->files()->in($targets)->name('*.php');

        foreach ($finder as $file) {
            $this->processFile($file);
        }
    }

    /**
     * @return array
     */
    protected function getTargets()
    {
        if ($target = $this->argument('target')) {
            return $target;
        }

        return [resource_path('views')];
    }

    /**
     * @param string $content
     */
    protected function extractTexts($content)
    {
        $content = preg_replace('/@{{.+?}}/', '', $content);
        $content = preg_replace('/{{.+?}}/', '', $content);
        $content = preg_replace('/{{{.+?}}}/', '', $content);
        $content = preg_replace('/{!!.+?!!}/', '', $content);
        $content = preg_replace('/{{--.+?--}}/', '', $content);
        $content = preg_replace('/<\?.+?\?>/', '', $content);
        $content = preg_replace('/<!--(.+?)-->/', '', $content);

        $texts = collect();

        foreach (static::DETECTION_REGEXES as $detectionRegex) {
            preg_match_all($detectionRegex, $content, $matches, PREG_SET_ORDER);

            $texts = $texts->merge($matches);
        }

        return $texts->filter(function ($value) {
            if (! trim($value['text'])) {
                return false;
            }

            return ! Str::contains($value['text'], ['@', '__']);
        });
    }

    /**
     * @param string $content
     * @param array $text
     * @param string $replacement
     * @return string
     */
    protected function replaceTextInContext($content, array $text, $replacement)
    {
        return str_replace(
            $text['context'],
            str_replace($text['text'], $replacement, $text['context']),
            $content
        );
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $file
     */
    protected function processFile(SplFileInfo $file)
    {
        $content = $originalContent = $file->getContents();
        $texts   = $this->extractTexts($content);

        if (count($texts) === 0) {
            return;
        }

        $this->info('>> '. $file->getRelativePathname());

        if (! $this->confirm('Would you like to translate this file?')) {
            return;
        }

        foreach ($texts as $text) {
            $target = $this->replaceTextInContext($text['line'], $text, '@te(\'variable\')');

            $this->comment('Found: '. $text['text']);
            $this->comment('+ '. $target);
            $this->comment('- '. $text['line']);

            $variable = $this->ask('Variable name', 'skip');

            if ($variable === 'skip') {
                continue;
            }

            $content = $this->replaceTextInContext($content, $text, '@te(\''. $variable .'\')');

            file_put_contents($file->getRealPath(), $content);
        }
    }
}
