<?php

namespace Exolnet\Translation\Editor\Tests\Unit\Middleware;

use Exolnet\Translation\Editor\Middleware\TranslationEditorInject;
use Exolnet\Translation\Editor\Tests\Integration\TestCase;
use Exolnet\Translation\Editor\TranslationEditor;
use Illuminate\Http\Request;
use Mockery as m;
use Symfony\Component\HttpFoundation\Response;

class TranslationEditorInjectTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\Editor\Middleware\TranslationEditorInject
     */
    protected $translationEditorInject;

    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $translationEditor;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationEditor = m::mock(TranslationEditor::class);
        $this->translationEditorInject = new TranslationEditorInject($this->translationEditor);
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function testHandleInjected(): void
    {
        $request = new Request();

        $this->translationEditor->shouldReceive('isEnabled')->once()->andReturn(true);

        $response = $this->translationEditorInject->handle($request, function ($req) {
            return new Response('</translation-editor></head>', 200, [
                'Content-Type' => 'html'
            ]);
        });
        $this->assertStringContainsString('<script', $response->getContent());
        $this->assertStringContainsString('<link', $response->getContent());
    }

    /**
     * @test
     * @return void
     * @throws \Exception
     */
    public function testHandleShouldNotInject(): void
    {
        $request = new Request();

        $this->translationEditor->shouldReceive('isEnabled')->once()->andReturn(false);

        $response = $this->translationEditorInject->handle($request, function ($req) {
            return new Response('', 200, []);
        });
        $this->assertStringNotContainsString('<script', $response->getContent());
        $this->assertStringNotContainsString('<link', $response->getContent());
    }
}
