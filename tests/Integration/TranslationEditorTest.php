<?php namespace Exolnet\Translation\Editor\Tests\Integration;

class TranslationEditorTest extends TestCase {

    /** @test */
    public function it_is_disabled_by_default()
    {
        $enabled = $this->app['config']->get('translation-editor.enabled');
        $this->assertFalse($enabled);
    }
}
