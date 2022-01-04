<?php

namespace Exolnet\Translation\Editor\Tests\Integration\Controllers;

use Exolnet\Translation\Editor\Controllers\AssetController;
use Exolnet\Translation\Editor\Tests\Integration\TestCase;

class AssetControllerTest extends TestCase
{
    /**
     * @var \Exolnet\Translation\Editor\Controllers\AssetController
     */
    protected $assetController;

    public function setUp(): void
    {
        parent::setUp();
        $this->assetController = new AssetController();
    }

    /**
     * @test
     * @return void
     */
    public function testJs(): void
    {
        $response = $this->assetController->js();

        $this->assertEquals('text/javascript', $response->headers->get('Content-Type'));
        $this->assertCacheResponse($response);
    }

    /**
     * @test
     * @return void
     */
    public function testCss(): void
    {
        $response = $this->assetController->css();

        $this->assertEquals('text/css', $response->headers->get('Content-Type'));
        $this->assertCacheResponse($response);
    }

    /**
     * @param $response
     * @return void
     */
    public function assertCacheResponse($response): void
    {
        $this->assertTrue($response->isCacheable());
        $this->assertEquals(31536000, $response->headers->getCacheControlDirective('max-age'));
        $this->assertEquals(31536000, $response->headers->getCacheControlDirective('s-maxage'));
        $dateTime = new \DateTime('+1 year');
        $this->assertEquals($response->getExpires()->format('y/m/d'), $dateTime->format('y/m/d'));
    }
}
