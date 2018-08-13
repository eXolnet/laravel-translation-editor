<?php

namespace Exolnet\Translation\Editor\Tests\Integration;

class TranslationEditorTest extends TestCase
{
    public function testItIsDisabledByDefault()
    {
        $enabled = $this->app['translation.editor']->isEnabled();

        $this->assertFalse($enabled);
    }
}
