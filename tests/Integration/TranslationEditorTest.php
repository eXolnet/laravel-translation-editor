<?php namespace Exolnet\Translation\Editor\Tests\Integration;

class TranslationEditorTest extends TestCase
{

    /** @test */
    public function itIsDisabledByDefault()
    {
        $enabled = $this->app['config']->get('translation-editor.enabled');
        $this->assertFalse($enabled);
    }
}
