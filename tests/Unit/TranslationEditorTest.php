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
        parent::setUp();
        $this->config = m::mock(Config::class);
        $this->translator = m::mock(Translator::class);
        $this->filesystem = m::mock(Filesystem::class);

        $this->editor= m::mock(TranslationEditor::class, [
            $this->config,
            $this->translator,
            $this->filesystem
        ])->makePartial();
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

    /**
     * @test
     * @return void
     */
    public function testRetrieveTranslation()
    {
        $this->editor->shouldReceive('getLocales')->once()->andReturn(['fr', 'es']);

        $this->translator->shouldReceive('has')->twice()->andReturn(true);
        $this->translator->shouldReceive('get')->twice()->andReturn('fr');

        $fakePath = 'test';
        $fakeLocale = 'fr';

        $expectedArray = [
            'path' => $fakePath,
            'source' => [
                'locale' => 'es',
                'translation' => 'fr',
            ],
            'destination' => [
                'locale' => $fakeLocale,
                'translation' => 'fr',
            ],
        ];
        $this->assertEquals($expectedArray, $this->editor->retrieveTranslation($fakePath, $fakeLocale));
    }

    /**
     * @test
     * @return void
     */
    public function testRetrieveTranslationNoLocale()
    {
        $this->editor->shouldReceive('getLocales')->once()->andReturn(['fr', 'es']);

        $this->translator->shouldReceive('has')->twice()->andReturn(true);
        $this->translator->shouldReceive('get')->twice()->andReturn('fr');

        $fakePath = 'test';

        $expectedArray = [
            'path' => $fakePath,
            'source' => [
                'locale' => 'fr',
                'translation' => 'fr',
            ],
            'destination' => [
                'locale' => null,
                'translation' => 'fr',
            ],
        ];
        $this->assertEquals($expectedArray, $this->editor->retrieveTranslation($fakePath));
    }

    /**
     * @test
     * @return void
     */
    public function testRetrieveTranslationGetLocalesNull()
    {
        $this->editor->shouldReceive('getLocales')->once()->andReturn([]);
        $this->translator->shouldReceive('has')->once()->andReturn(false);

        $fakePath = 'test';

        $expectedArray = [
            'path' => $fakePath,
            'source' => [
                'locale' => null,
                'translation' => null,
            ],
            'destination' => [
                'locale' => null,
                'translation' => null,
            ],
        ];
        $this->assertEquals($expectedArray, $this->editor->retrieveTranslation($fakePath));
    }
}
