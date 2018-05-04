<?php namespace Exolnet\Translation\Editor\Console;

use Illuminate\Console\Command;

class TranslateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'i18n:translate {--extract} {--review} {--filter=} {locale*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pass each translation variables to translate them.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        //
    }

    /**
     * @return array
     */
    protected function getLocales()
    {
        if ($locale = $this->argument('locale')) {
            return $locale;
        }

        return $this->translationEditor->detectLocales();
    }
}
