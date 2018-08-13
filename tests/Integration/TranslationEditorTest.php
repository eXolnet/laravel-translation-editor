<?php

namespace Exolnet\Translation\Editor\Tests\Integration;

class TranslationEditorTest extends TestCase
{
    public function testItIsDisabledByDefault()
    {
        $enabled = $this->app['config']->get('translation-editor.enabled');
        $this->assertFalse($enabled);
    }
}
