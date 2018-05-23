<?php namespace Exolnet\Translation\Editor\Tests\Unit;

use Exolnet\Translation\Editor\TranslationEditor;
use Exolnet\Translation\Editor\Translator;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Config\Repository as Config;
use Mockery as m;

class TranslationEditorTest extends UnitTest {

    /**
     * @var \Mockery\MockInterface|\Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Mockery\MockInterface|\Exolnet\Translation\Editor\Translator
     */
    protected $translator;

    /**
     * @var \Mockery\MockInterface|\Exolnet\Translation\Editor\TranslationEditor
     */
    protected $editor;

    /**
     * @var \Mockery\MockInterface|\Illuminate\Contracts\Config\Repository
     */
    protected $config;

    public function setUp()
    {
        $this->filesystem = m::mock(Filesystem::class);
        $this->translator = m::mock(Translator::class);
        $this->config = m::mock(Config::class);

        $this->editor = new TranslationEditor($this->filesystem, $this->translator, $this->config);
    }

    public function testIsEditorDisabledByDefault()
    {
        $this->config->shouldReceive('get')
            ->with('translation-editor.enabled')
            ->andReturn(false);

        $this->assertFalse($this->editor->isEnabled());
    }

    public function testGetWithEditorDisabled()
    {
        $this->config->shouldReceive('get')
            ->with('translation-editor.enabled')
            ->andReturn(false);

        $this->translator->shouldReceive('getFromJson')
            ->withArgs(['default.title', [], null])
            ->andReturn('Title');

        $this->assertEquals('Title', $this->editor->get('default.title'));
    }

    public function testGetWithEditorEnabled()
    {
        $this->config->shouldReceive('get')
            ->with('translation-editor.enabled')
            ->andReturn(true)
            ->once();

        $this->config->shouldReceive('get')
            ->with('app.locale')
            ->andReturn('en')
            ->once();

        $this->translator->shouldReceive('getFromJson')
            ->withArgs(['default.title', [], null])
            ->andReturn('Title');

        $result = $this->editor->get('default.title');
        $this->assertStringStartsWith('<translation-editor', $result);
        $this->assertStringEndsWith('</translation-editor>', $result);
        $this->assertContains('locale="en"', $result);
        $this->assertContains('path="default.title"', $result);
        $this->assertContains('Title', $result);
    }
}
