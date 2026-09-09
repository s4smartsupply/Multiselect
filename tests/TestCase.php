<?php

namespace Xylo\MultiselectTwo\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Xylo\MultiselectTwo\XyloMultiselectTwoServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            XyloMultiselectTwoServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
    }
}
