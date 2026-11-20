<?php

declare(strict_types=1);

// File: tests/DocsSnippetsTest.php | Map: read the Livewire and Blade tabs of the OmniaUI docs -> Livewire: load the class, write the view,
// render it, check bound properties and run its actions -> Blade: render every Blade tab -> SKIP: pages/tabs that cannot run here

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
 * `<oc-code lang="blade">` blocks of every page under ui/components and ui/blocks and runs them against this package.
 * A Livewire tab is a component class and its view, split at the `{{-- resources/views/… --}}` line.
 */
class DocsSnippetsTest extends TestCase
{
    /**
     * What cannot run here, with the reason: a page ("components/webicon") or one tab ("blade:components/alert #4", numbered
     * per language on the page as in the data set names). Everything else under ui/components and ui/blocks is read.
     *
     * @var array<string, string>
     */
    private const SKIP = [
        'components/webicon' => 'webicons read the Iconify sets installed in the app; this test app has none, so they render nothing',
        // Hand-written Blade tabs that show a component fed by controller data (a free $variable), as in a real view.
        'blade:components/alert #4' => 'uses $help from the controller',
        'blade:components/calendar #3' => 'uses $date from the controller',
        'blade:components/code-plate #1' => 'uses $qrTest from the controller',
        'blade:components/code-plate #2' => 'uses $qrTest from the controller',
        'blade:components/countdown #1' => 'uses $round from the controller',
        'blade:components/empty-state #2' => 'uses $distance from the controller',
        'blade:components/logo #1' => 'uses $customer from the controller',
        'blade:components/logo #2' => 'uses $logo from the controller',
        'blade:components/status-dot #4' => 'uses $state from the controller',
        'blade:blocks/content-blocks #1' => 'uses $step from the controller',
        'blade:blocks/route-progress #1' => 'uses $route from the controller',
        'blade:blocks/route-steps #1' => 'uses $route from the controller',
        'blade:blocks/step-view #1' => 'uses $step from the controller',
        'blade:blocks/type-picker #1' => 'uses $step from the controller',
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
        $root = dirname(__DIR__, 3)."/apps/site/src/content/ui";
        foreach (array_merge(glob("{$root}/components/*.html") ?: [], glob("{$root}/blocks/*.html") ?: []) as $file) {
            $page = basename(dirname($file))."/".basename($file, ".html");
            if (isset(self::SKIP[$page])) {
                continue;
            }
            preg_match_all('/<oc-code lang="'.$lang.'">(.*?)<\/oc-code>/s', (string) file_get_contents($file), $m);
            foreach ($m[1] as $i => $code) {
                $key = "{$page} #".($i + 1);
                if (! isset(self::SKIP["{$lang}:{$key}"])) {
                    $out[$key] = [html_entity_decode($code, ENT_QUOTES | ENT_HTML5, 'UTF-8')];
                }
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
        // /u: without it \R also matches a lone 0x85 byte (NEL) inside a UTF-8 character such as ★ (E2 98 85)
        $lines = preg_split('/\R/u', $snippet);
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
        $this->assertMatchesRegularExpression('/<[a-z][\w-]*[\s>]/', $html, 'the tab renders markup');
        $this->assertStringNotContainsString('<x-oc', $html, 'every <x-oc::…> tag is a component');

        // Every bound property exists on the class …
        preg_match_all('/(?:(?<![\w-])model|wire:model(?:\.[\w.]+)?)="([\w.]+)"/', $blade, $models);
        foreach (array_unique($models[1]) as $property) {
            $this->assertTrue(property_exists($fqcn, explode('.', $property)[0]), "{$c[1]} has \${$property}");
        }

        // … and every action runs (validation errors are fine, exceptions are not).
        // (?<![\w-]): a data-action="up" in plain markup is not a component attribute.
        preg_match_all('/(?:wire:click|wire:submit|(?<![\w-])action|(?<![\w-])complete)="(\w+)(?:\(([^)]*)\))?"/', $blade, $calls, PREG_SET_ORDER);
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

        $this->assertMatchesRegularExpression('/<[a-z][\w-]*[\s>]/', $html, 'the tab renders markup');
        $this->assertStringNotContainsString('<x-oc', $html, 'every <x-oc::…> tag is a component');
    }
}
