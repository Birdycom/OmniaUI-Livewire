<?php

declare(strict_types=1);

// File: src/OmniacssLivewireServiceProvider.php | Map: anonymous components -> views -> publishing

namespace Omniacss\Livewire;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class OmniacssLivewireServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $prefix = (string) config('omniacss.prefix', 'oc');

        // Same `oc` prefix as birdycom/omniaui-laravel: <x-oc::wire.modal>, <x-oc::toast-stack />
        Blade::anonymousComponentPath(self::path('resources/views/components'), $prefix);

        // View of the abstract DataTable component.
        $this->loadViewsFrom(self::path('resources/views'), 'omniacss-livewire');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                self::path('resources/views/livewire') => resource_path('views/vendor/omniacss-livewire'),
            ], 'omniacss-livewire-views');
        }
    }

    public static function path(string $relative = ''): string
    {
        return dirname(__DIR__).($relative !== '' ? '/'.ltrim($relative, '/') : '');
    }
}
