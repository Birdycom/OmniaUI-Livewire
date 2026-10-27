<?php

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

use Illuminate\Support\Facades\Blade;
use Livewire\LivewireServiceProvider;
use Omniacss\Laravel\OmniacssServiceProvider;
use Omniacss\Livewire\OmniacssLivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            OmniacssServiceProvider::class,
            OmniacssLivewireServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    /** Renders a Blade string and collapses whitespace so assertions stay readable. */
    protected function render(string $blade, array $data = []): string
    {
        return trim(preg_replace('/\s+/', ' ', Blade::render($blade, $data)));
    }
}
