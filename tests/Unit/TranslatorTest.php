<?php

namespace Exolnet\Translation\Editor\Tests\Unit;

use Exception;
use Exolnet\Translation\Editor\Translator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class TranslatorTest extends TestCase
{
    /** @var \Mockery\Mock|\Exolnet\Translation\Editor\Translator*/
    private $translator;

    public function setUp(): void
    {
        $this->translator = m::mock(Translator::class)->makePartial();
        $this->translator->setLoaded([
            'label' => [
                'hour' => [
                    'fr' => 'heure',
                    'en' => 'hour',
                ],
                'results' => [
                    'fr' => 'résultats',
                    'en' => 'results',
                ],
            ],
            'errors' => [
                'no_error' => [
                    'fr' => 'aucune erreur',
                    'en' => 'no error'
                ]
            ]
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function testGetALlVariables(): void
    {
        $actualArray = $this->translator->getAllVariables('fr', 'label');
        $expectedArray = [
            'hour' => 'heure',
            'results' => 'résultats'
        ];
        $this->assertEquals($expectedArray, $actualArray);
    }

    /**
     * @test
     * @return void
     */
    public function testGetAllDefinedNames(): void
    {
        $actualArray = $this->translator->getAllDefinedNames('fr', 'label');
        $expectedArray = [
            'hour',
            'results'
        ];
        $this->assertEquals($expectedArray, $actualArray);
    }

    /**
     * @test
     * @return void
     */
    public function testUnloadAll(): void
    {
        $this->translator->unloadAll();

        $this->tryCatchUndefinedIndex('fr', 'label');
        $this->tryCatchUndefinedIndex('en', 'label');
        $this->tryCatchUndefinedIndex('fr', 'error');
    }

    /**
     * @param string $locale
     * @param string $namespace
     */
    private function tryCatchUndefinedIndex(string $locale, string $namespace): void
    {
        try {
            $this->translator->getAllVariables($locale, $namespace);
        } catch (Exception $exception) {
            $this->assertEquals("Undefined index: " . $namespace, $exception->getMessage());
        }
    }
}
