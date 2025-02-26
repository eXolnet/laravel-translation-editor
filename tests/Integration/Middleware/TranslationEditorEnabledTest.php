<?php

namespace Exolnet\Translation\Editor\Tests\Unit\Middleware;

use Exolnet\Translation\Editor\Middleware\TranslationEditorEnabled;
use Exolnet\Translation\Editor\Tests\Integration\TestCase;
use Exolnet\Translation\Editor\TranslationEditor;
use Illuminate\Http\Request;
use Mockery as m;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TranslationEditorEnabledTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\Editor\Middleware\TranslationEditorEnabled
     */
    protected $translationEditorEnabled;

    /**
     * @var \Exolnet\Translation\Editor\TranslationEditor|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected $translationEditor;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationEditor = m::mock(TranslationEditor::class);
        $this->translationEditorEnabled = new TranslationEditorEnabled($this->translationEditor);
    }

    /**
     * @return void
     */
    public function testHandle(): void
    {
        $request = new Request();

        $this->translationEditor->shouldReceive('isEnabled')->once()->andReturn(true);
        $this->translationEditorEnabled->handle($request, function ($req) {
        });
    }

    /**
     * @return void
     */
    public function testHandleAbort(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $request = new Request();

        $this->translationEditor->shouldReceive('isEnabled')->once()->andReturn(false);
        $this->translationEditorEnabled->handle($request, function ($req) {
        });
    }
}
