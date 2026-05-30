<?php

namespace Aghfatehi\Tamara\Tests;

use Orchestra\Testbench\TestCase;

class TamaraControllerTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Aghfatehi\Tamara\TamaraServiceProvider::class,
        ];
    }

    /** @test */
    public function it_can_access_cancel_route()
    {
        $response = $this->get(route('tamara.cancel'));
        $response->assertStatus(302);
    }

    /** @test */
    public function it_can_access_failure_route()
    {
        $response = $this->get(route('tamara.failure'));
        $response->assertStatus(302);
    }
}
