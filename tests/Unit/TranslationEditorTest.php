<?php

namespace Exolnet\Translation\Editor\Tests\Unit;

use Exolnet\Translation\Editor\TranslationEditor;
use Exolnet\Translation\Editor\Translator;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Mockery as m;

class TranslationEditorTest extends UnitTest
{
    /**
     * @var \Mockery\MockInterface|\Exolnet\Translation\Editor\TranslationEditor
     */
    protected $editor;

    /**
     * @var \Mockery\MockInterface|\Illuminate\Contracts\Config\Repository
     */
    protected $config;

    /**
     * @var \Mockery\MockInterface|\Exolnet\Translation\Editor\Translator
     */
    protected $translator;

    /**
     * @var \Mockery\MockInterface|\Illuminate\Filesystem\Filesystem
     */
    protected $filesystem;

    public function setUp(): void
    {
        $this->config = m::mock(Config::class);
        $this->translator = m::mock(Translator::class);
        $this->filesystem = m::mock(Filesystem::class);

        $this->editor = new TranslationEditor($this->config, $this->translator, $this->filesystem);
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

        $translatorMethodGet = method_exists($this->translator, 'getFromJson') ? 'getFromJson' : 'get';

        $this->translator->shouldReceive($translatorMethodGet)
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

        $translatorMethodGet = method_exists($this->translator, 'getFromJson') ? 'getFromJson' : 'get';

        $this->translator->shouldReceive($translatorMethodGet)
            ->withArgs(['default.title', [], null])
            ->andReturn('Title');

        $result = $this->editor->get('default.title');
        $this->assertStringStartsWith('<translation-editor', $result);
        $this->assertStringEndsWith('</translation-editor>', $result);
        $this->assertTrue(strpos($result, 'locale="en"') !== false);
        $this->assertTrue(strpos($result, 'path="default.title"') !== false);
        $this->assertTrue(strpos($result, 'Title') !== false);
    }

    /**
     * @test
     * @return void
     */
    public function testDetectLocales(): void
    {
        $this->config->shouldReceive('get')
            ->with('app.supported_locales')
            ->once()
            ->andReturnNull();

        $this->config->shouldReceive('get')
            ->with('app.locale')
            ->once()
            ->andReturn('en');

        $this->assertEquals(['en'], $this->editor->detectLocales());

        $this->config->shouldReceive('get')
            ->with('app.supported_locales')
            ->once()
            ->andReturn(['fr', 'en']);

        $this->assertEquals(['fr', 'en'], $this->editor->detectLocales());
    }
}
