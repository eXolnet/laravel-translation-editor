<?php

namespace Exolnet\Translation\Editor\Tests\Integration\Controllers;

use Exolnet\Translation\Editor\Controllers\TranslationController;
use Exolnet\Translation\Editor\Tests\Integration\TestCase;
use Exolnet\Translation\Editor\TranslationEditor;
use Illuminate\Http\Request;
use Mockery as m;

class TranslationControllerTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor|\Mockery\MockInterface
     */
    protected $translationEditor;

    /**
     * @var \Exolnet\Translation\Editor\Controllers\TranslationController
     */
    protected $translationController;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationEditor = m::mock(TranslationEditor::class);
        $this->translationController = new TranslationController($this->translationEditor);
    }

    /**
     * @test
     * @return void
     */
    public function testShow(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('get')->with('locale')->once()->andReturn('fr');
        $request->shouldReceive('get')->with('path')->once()->andReturn('/');
        $returnedArray = [
            'path' => '/',
            'source' => [
                'locale' => 'fr',
                'translation' => 'fr',
            ],
            'destination' => [
                'locale' => 'fr',
                'translation' => 'fr',
            ],
        ];
        $this->translationEditor
            ->shouldReceive('retrieveTranslation')
            ->withArgs(['/', 'fr'])
            ->once()
            ->andReturn($returnedArray);

        $response = $this->translationController->show($request);
        $this->assertEquals(json_encode($returnedArray), $response->getContent());
    }

    /**
     * @test
     * @return void
     */
    public function testStore(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('get')->with('locale')->once()->andReturn('fr');
        $request->shouldReceive('get')->with('path')->once()->andReturn('/');
        $request->shouldReceive('get')->with('translation')->once()->andReturn('test');

        $this->translationEditor
            ->shouldReceive('storeTranslation')
            ->withArgs(['/', 'test', 'fr'])
            ->once();

        $response = $this->translationController->store($request);
        $this->assertEquals(json_encode(['compiled' => 'test']), $response->getContent());
    }
}
