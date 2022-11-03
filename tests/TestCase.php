<?php

namespace MadWeb\Initializer\Test;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PHPUnit\Runner\Version;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    /**
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [\MadWeb\Initializer\InitializerServiceProvider::class];
    }

    /**
     * Added for support backward capability with PHPUnit < 8.0.
     */
    public static function assertStringContainsString(string $needle, string $haystack, string $message = ''): void
    {
        if (version_compare(Version::series(), '8.0') >= 0) {
            parent::assertStringContainsString($needle, $haystack, $message = '');
        } else {
            parent::assertContains($needle, $haystack, $message);
        }
    }
}
