<?php

declare(strict_types=1);

// File: tests/DocsSnippetsTest.php | Map: read the Livewire and Blade tabs of the OmniaUI docs -> Livewire: load the class, write the view,
// render it, check bound properties and run its actions -> Blade: render every tab of the same pages

namespace Omniacss\Livewire\Tests;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Livewire\Livewire;
use Omniacss\Livewire\DataTable;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The code tabs on omniaui.eu must work as they are copied. This test reads the `<oc-code lang="livewire">` and
 * `<oc-code lang="blade">` blocks of the interactive component pages and runs them against this package.
 * A Livewire tab is a component class and its view, split at the `{{-- resources/views/… --}}` line.
 */
class DocsSnippetsTest extends TestCase
{
    private const PAGES = [
        'forms', 'segmented', 'otp-input', 'tag-input', 'toggle-field', 'radio', 'native-select',
        'modal', 'sheet', 'alert-dialog', 'confirm-dialog', 'toast', 'toast-stack', 'data-table',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Named routes and the session that the plain Blade tabs use (route(), old(), session(), @csrf).
        foreach (['contact.send' => '/contact', 'newsletter.subscribe' => '/newsletter', 'profile.update' => '/profile', 'columns.update' => '/columns', 'projects.destroy' => '/projects/{project}'] as $name => $uri) {
            Route::post($uri, fn () => '')->name($name);
        }
        app('router')->getRoutes()->refreshNameLookups();
        $this->app['request']->setLaravelSession($this->app['session.store']);

        Schema::create('articles', function ($table): void {
            $table->id();
            $table->string('sku');
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->string('supplier');
            $table->string('status');
        });
        DB::table('articles')->insert(['sku' => 'SET-HINGE-1105', 'name' => 'Hinge 1105', 'price' => 5.15, 'supplier' => 'Supplier 1', 'status' => 'Active']);
    }

    /** @return array<string, array{string}> */
    private static function blocks(string $lang): array
    {
        $out = [];
        foreach (self::PAGES as $page) {
            $file = dirname(__DIR__, 3)."/apps/site/src/content/ui/components/{$page}.html";
            if (! is_file($file)) {
                continue;
            }
            preg_match_all('/<oc-code lang="'.$lang.'">(.*?)<\/oc-code>/s', (string) file_get_contents($file), $m);
            foreach ($m[1] as $i => $code) {
                $out["{$page} #".($i + 1)] = [html_entity_decode($code, ENT_QUOTES | ENT_HTML5, 'UTF-8')];
            }
        }

        // Outside the monorepo (the package on its own) the docs are not there.
        return $out ?: ['docs not in this checkout' => ['']];
    }

    public static function livewireTabs(): array
    {
        return self::blocks('livewire');
    }

    public static function bladeTabs(): array
    {
        return self::blocks('blade');
    }

    /** @return array{0: string, 1: string, 2: ?string} PHP class, Blade part, path of the Blade part */
    private function split(string $snippet): array
    {
        $lines = preg_split('/\R/', $snippet);
        $at = null;
        $path = null;
        foreach ($lines as $n => $line) {
            if (preg_match('/^\{\{--\s*(resources\/views\/\S+\.blade\.php)\s*--\}\}\s*$/', $line, $m)) {
                $at = $n;
                $path = $m[1];
                break;
            }
        }
        $this->assertNotNull($at, 'a Livewire tab names its view: {{-- resources/views/... --}}');

        return [implode("\n", array_slice($lines, 0, $at)), implode("\n", array_slice($lines, $at)), $path];
    }

    #[DataProvider('livewireTabs')]
    public function test_livewire_tab_runs(string $snippet): void
    {
        if ($snippet === '') {
            $this->markTestSkipped('The OmniaUI docs are not part of this checkout.');
        }

        [$php, $blade, $path] = $this->split($snippet);
        $this->assertStringStartsWith('<?php', $php);
        $this->assertMatchesRegularExpression('/^namespace App\\\\Livewire;$/m', $php);
        $this->assertSame(1, preg_match('/^class (\w+) extends (\w+)/m', $php, $c), 'one component class');

        // A unique class name per snippet, so two tabs may use the same name.
        $class = $c[1].'_'.substr(md5($snippet), 0, 8);
        $fqcn = 'App\\Livewire\\'.$class;
        if (! class_exists($fqcn, false)) {
            eval(preg_replace('/^class '.$c[1].' /m', "class {$class} ", substr($php, 5), 1));
        }

        if (is_subclass_of($fqcn, DataTable::class)) {
            Livewire::test($fqcn)->assertSee('Hinge 1105')->assertSeeHtml('oc-block-datatable');

            return;
        }

        $this->assertStringStartsWith('resources/views/livewire/', $path);
        $dir = sys_get_temp_dir().'/oc-docs-livewire/'.substr(md5($snippet), 0, 8);
        $file = $dir.'/'.substr($path, strlen('resources/views/'));
        @mkdir(dirname($file), 0777, true);
        file_put_contents($file, $blade);
        View::getFinder()->prependLocation($dir);
        View::getFinder()->flush();

        $test = Livewire::test($fqcn);
        $html = $test->html();
        $this->assertStringContainsString('oc-', $html);
        $this->assertStringNotContainsString('<x-oc', $html, 'every <x-oc::…> tag is a component');

        // Every bound property exists on the class …
        preg_match_all('/(?:\bmodel|wire:model(?:\.[\w.]+)?)="([\w.]+)"/', $blade, $models);
        foreach (array_unique($models[1]) as $property) {
            $this->assertTrue(property_exists($fqcn, explode('.', $property)[0]), "{$c[1]} has \${$property}");
        }

        // … and every action runs (validation errors are fine, exceptions are not).
        preg_match_all('/(?:wire:click|wire:submit|\baction|\bcomplete)="(\w+)(?:\(([^)]*)\))?"/', $blade, $calls, PREG_SET_ORDER);
        foreach ($calls as $call) {
            $this->assertTrue(method_exists($fqcn, $call[1]), "{$c[1]}::{$call[1]}() exists");
            $args = isset($call[2]) && $call[2] !== '' ? array_map(fn ($a) => trim($a, " '\""), explode(',', $call[2])) : [];
            $test->call($call[1], ...$args);
        }
    }

    #[DataProvider('bladeTabs')]
    public function test_blade_tab_renders(string $snippet): void
    {
        if ($snippet === '') {
            $this->markTestSkipped('The OmniaUI docs are not part of this checkout.');
        }

        $html = Blade::render($snippet, [
            'columns' => [['key' => 'sku', 'header' => 'Article ID'], ['key' => 'price', 'header' => 'Price', 'numeric' => true]],
            'rows' => [['key' => 'hinge', 'status' => 'active', 'cells' => ['sku' => 'SET-HINGE-1105', 'price' => '5,15'], 'values' => ['price' => '5.15']]],
            'project' => 1,
        ]);

        $this->assertStringContainsString('oc-', $html);
        $this->assertStringNotContainsString('<x-oc', $html, 'every <x-oc::…> tag is a component');
    }
}
