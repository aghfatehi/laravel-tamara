<?php

namespace Aghfatehi\Tamara\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            \Aghfatehi\Tamara\TamaraServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        config()->set('tamara.sandbox', true);
        config()->set('tamara.api_token', 'test-token');
    }
}
