<?php

namespace Exolnet\Translation\Editor\Tests\Integration;

use Exolnet\Translation\Editor\TranslationEditor;
use Exolnet\Translation\Editor\Translator;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Mockery as m;

class TranslationEditorTest extends TestCase
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

        $this->editor = new TranslationEditor($this->config, $this->translator, $this->filesystem);
    }

    public function testItIsDisabledByDefault()
    {
        $enabled = $this->app['translation.editor']->isEnabled();

        $this->assertFalse($enabled);
    }

    /**
     * @return void
     */
    public function testGetLocales()
    {
        $this->translator->shouldReceive('getFallback')
            ->once()
            ->andReturn('en');

        $this->filesystem->shouldReceive('directories')
            ->once()
            ->andReturn(['fr', 'es']);

        $this->assertEquals(['en', 'fr', 'es'], $this->editor->getLocales());
    }

    /**
     * @return void
     */
    public function testStoreTranslation(): void
    {
        $this->translator->shouldReceive('has')->once()->andReturn(true);
        $this->translator->shouldReceive('get')->once()->andReturn(['fr', 'en', 'es']);

        $this->filesystem->shouldReceive('put')->once()->andReturn(true);
        $this->editor->storeTranslation('tests.test', 'bonjour', 'fr');
    }
}
