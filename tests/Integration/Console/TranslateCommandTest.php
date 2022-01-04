<?php

namespace Exolnet\Translation\Editor\Tests\Integration\Console;

use Exolnet\Translation\Editor\Console\TranslateCommand;
use Exolnet\Translation\Editor\Tests\Integration\TestCase;
use Exolnet\Translation\Editor\TranslationEditor;
use Mockery as m;
use Symfony\Component\Console\Input\Input;
use Illuminate\Console\OutputStyle;

class TranslateCommandTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor|\Mockery\MockInterface
     */
    protected $translationEditor;

    /**
     * @var \Exolnet\Translation\Editor\Console\TranslateCommand
     */
    protected $translateCommand;

    /**
     * @var \Symfony\Component\Console\Input\Input\|\Mockery\MockInterface
     */
    protected $input;

    /**
     * @var \Illuminate\Console\OutputStyle|\Mockery\MockInterface
     */
    protected $output;

    public function setUp(): void
    {
        parent::setUp();

        $this->translationEditor = m::mock(TranslationEditor::class);
        $this->input = m::mock(Input::class);
        $this->output = m::mock(OutputStyle::class);

        $this->translateCommand = new TranslateCommand($this->translationEditor);
        $this->translateCommand->setInput($this->input);
        $this->translateCommand->setOutput($this->output);
    }

    public function testHandle()
    {
        $this->input->shouldReceive('getOption')->once()->andReturn('tests/TestFiles');
        $this->input->shouldReceive('getArgument')->once()->andReturn(['fr', 'es']);
        $this->output->shouldReceive('writeln')->twice();
        $this->translationEditor->shouldReceive('loadAllGroups')->twice();
        $this->translationEditor->shouldReceive('getAllDefinedNames')->twice()->andReturn([]);
        $this->output->shouldReceive('ask')->times(8)->andReturn('replaced text');
        //4 different variables read from tests/Testfiles/translateCommadRegex called 2 times each
        //cause 2 locales returned by mocked getArgument (4 * 2 = 8 times storeTranslation is called)
        $this->translationEditor->shouldReceive('storeTranslation')->times(8);

        $this->translateCommand->handle();
    }
}
