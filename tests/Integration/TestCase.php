<?php namespace Exolnet\Translation\Editor\Tests\Integration;

use Exolnet\Translation\Editor\TranslationEditorServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp()
    {
        parent::setUp();
    }
    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            TranslationEditorServiceProvider::class,
        ];
    }
}
