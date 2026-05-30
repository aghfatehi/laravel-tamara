<?php

namespace Aghfatehi\Tamara\Tests;

use Aghfatehi\Tamara\Services\TamaraService;
use Orchestra\Testbench\TestCase;

class TamaraServiceTest extends TestCase
{
    protected TamaraService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TamaraService();
    }

    /** @test */
    public function it_resolves_sandbox_base_url()
    {
        config(['tamara.sandbox' => true]);
        $url = $this->service->baseUrl();

        $this->assertEquals('https://api-sandbox.tamara.co', $url);
    }

    /** @test */
    public function it_resolves_production_base_url()
    {
        config(['tamara.sandbox' => false]);
        $url = $this->service->baseUrl();

        $this->assertEquals('https://api.tamara.co', $url);
    }
}
