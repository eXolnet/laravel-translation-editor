<?php

namespace Exolnet\Translation\Editor\Tests\Integration\Console;

use Exolnet\Translation\Editor\Console\DetectCommand;
use Exolnet\Translation\Editor\Tests\Integration\TestCase;
use Exolnet\Translation\Editor\TranslationEditor;
use Illuminate\Contracts\Container\Container;
use Mockery as m;
use Symfony\Component\Console\Input\Input;
use Illuminate\Console\OutputStyle;
use Symfony\Component\Finder\Finder;

class DetectCommandTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor|\Mockery\MockInterface
     */
    protected $translationEditor;

    /**
     * @var \Exolnet\Translation\Editor\Console\DetectCommand
     */
    protected $detectCommand;

    /**
     * @var \Symfony\Component\Console\Input\Input\|\Mockery\MockInterface
     */
    protected $input;

    /**
     * @var \Illuminate\Console\OutputStyle|\Mockery\MockInterface
     */
    protected $output;

    /**
     * @var \Illuminate\Contracts\Container\Container|\Mockery\MockInterface
     */
    protected $laravel;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    public function setUp(): void
    {
        parent::setUp();

        $this->translationEditor = m::mock(TranslationEditor::class);
        $this->input = m::mock(Input::class);
        $this->output = m::mock(OutputStyle::class);
        $this->laravel = m::mock(Container::class);

        $this->finder = new finder();
        $this->detectCommand = new DetectCommand($this->translationEditor);
        $this->detectCommand->setInput($this->input);
        $this->detectCommand->setOutput($this->output);
        $this->detectCommand->setLaravel($this->laravel);
    }

    public function testHandle()
    {
        $this->input->shouldReceive('getArgument')->once()->andReturn('tests/TestFiles');
        $this->output->shouldReceive('writeln')->times(7);
        $this->output->shouldReceive('confirm')->once()->andReturn(true);
        $this->translationEditor->shouldReceive('findVariablesForText')->twice()->andReturn([]);
        $this->input->shouldReceive('getOption')->times(6);
        $this->laravel->shouldReceive('getLocale')->times(6)->andReturn('fr');
        $this->output->shouldReceive('askQuestion')->twice()->andReturn('replaced text');
        $this->translationEditor->shouldReceive('getAllDefinedNames')->twice()->andReturn([]);
        $this->translationEditor->shouldReceive('storeTranslation')->twice();

        $this->assertStringContainsString(
            'DetectCommand title="testing first regex"',
            file_get_contents(getcwd() . '/tests/TestFiles/detectCommandRegex.php')
        );
        $this->assertStringContainsString(
            '> testing second regex </',
            file_get_contents(getcwd() . '/tests/TestFiles/detectCommandRegex.php')
        );

        $this->detectCommand->handle($this->finder);

        $this->assertStringContainsString(
            'DetectCommand title="@te(\'replaced text\')"',
            file_get_contents(getcwd() . '/tests/TestFiles/detectCommandRegex.php')
        );
        $this->assertStringContainsString(
            '> @te(\'replaced text\') </',
            file_get_contents(getcwd() . '/tests/TestFiles/detectCommandRegex.php')
        );

        //Reset the content of the file
        file_put_contents(
            getcwd() . '/tests/TestFiles/detectCommandRegex.php',
            '<?php
\'DetectCommand title="testing first regex"\';
\'> testing second regex </\';
'
        );
    }
}
