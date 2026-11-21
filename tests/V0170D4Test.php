<?php

declare(strict_types=1);

// File: tests/V0170D4Test.php
// Map: trash pills for a SoftDeletes model (active / including trash / trash only, URL state, page reset) -> no pills for a
//      plain query -> column chooser (hide, show, locked column) -> skeleton body + wire:loading on the section

namespace Omniacss\Livewire\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Omniacss\Livewire\DataTable;

class V0170D4Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('d4_items', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('sku');
            $table->decimal('price', 8, 2)->default(0);
            $table->softDeletes();
        });
        foreach (['Item 1', 'Item 2', 'Item 3', 'Item 4'] as $i => $name) {
            DB::table('d4_items')->insert(['name' => $name, 'sku' => 'SKU-'.($i + 1), 'price' => ($i + 1) * 5]);
        }
        DB::table('d4_items')->where('name', 'Item 4')->update(['deleted_at' => now()]);
    }

    public function test_trash_pills_show_for_a_soft_deleting_model(): void
    {
        Livewire::test(D4ItemsTable::class)
            ->assertSeeHtml('class="oc-filter-pills" role="group" aria-label="Trash filter"')
            ->assertSeeHtml('wire:click="setTrashed(\'deleted\')"')
            ->assertSeeHtml('aria-current="true">Active only')
            ->assertSee('Item 1')
            ->assertDontSee('Item 4');
    }

    public function test_trash_only_and_including_trash(): void
    {
        Livewire::test(D4ItemsTable::class)
            ->call('setTrashed', 'deleted')
            ->assertSet('trashed', 'deleted')
            ->assertSee('Item 4')
            ->assertDontSee('Item 1')
            ->assertSeeHtml('aria-current="true">Trash only')
            ->call('setTrashed', 'all')
            ->assertSee('Item 1')
            ->assertSee('Item 4')
            ->assertSee('Showing 1 to 4 of 4 entries');
    }

    public function test_unknown_trash_value_falls_back_to_active(): void
    {
        Livewire::test(D4ItemsTable::class)
            ->call('setTrashed', 'archived')
            ->assertSet('trashed', 'active')
            ->assertDontSee('Item 4');
    }

    public function test_trash_choice_resets_the_page(): void
    {
        Livewire::test(D4ItemsTable::class)
            ->set('perPage', 2)
            ->call('gotoPage', 2)
            ->assertSee('Item 3')
            ->call('setTrashed', 'deleted')
            ->assertSee('Item 4')
            ->assertSee('Showing 1 to 1 of 1 entries');
    }

    public function test_no_pills_for_a_plain_query(): void
    {
        Livewire::test(D4PlainTable::class)
            ->assertDontSeeHtml('oc-filter-pills')
            ->assertDontSeeHtml('oc-data-table-colpick')
            ->assertSee('Item 4');
    }

    public function test_column_chooser_hides_and_shows_a_column(): void
    {
        Livewire::test(D4ItemsTable::class)
            ->assertSeeHtml('class="oc-dropdown-menu oc-data-table-colpick"')
            ->assertSeeHtml('wire:click="toggleColumn(\'sku\')"')
            ->assertSee('SKU-1')
            ->call('toggleColumn', 'sku')
            ->assertSet('hidden', ['sku'])
            ->assertDontSee('SKU-1')
            ->assertSee('Item 1')
            ->call('toggleColumn', 'sku')
            ->assertSet('hidden', [])
            ->assertSee('SKU-1');
    }

    public function test_a_locked_column_cannot_be_hidden(): void
    {
        Livewire::test(D4ItemsTable::class)
            ->call('toggleColumn', 'name')
            ->assertSet('hidden', [])
            ->call('toggleColumn', 'nope')
            ->assertSet('hidden', [])
            ->assertSee('Item 1');
    }

    public function test_skeleton_body_and_loading_state(): void
    {
        $html = Livewire::test(D4ItemsTable::class)->html();
        $this->assertStringContainsString('wire:loading.delay.attr="aria-busy"', $html);
        $this->assertStringContainsString('<tbody class="oc-data-table-skeleton-body" aria-hidden="true">', $html);
        $this->assertStringContainsString('<tbody class="oc-block-datatable-rows">', $html);
        $this->assertSame(4, substr_count($html, 'class="oc-data-table-skeleton"'), 'one skeleton row per row of the page (max 10)');
        $this->assertStringContainsString('<span class="oc-skeleton oc-data-table-skeleton-bar"></span>', $html);
    }
}

class D4Item extends Model
{
    use SoftDeletes;

    protected $table = 'd4_items';

    public $timestamps = false;
}

class D4ItemsTable extends DataTable
{
    public int $perPage = 4;

    protected function columns(): array
    {
        return [
            'name' => ['label' => 'Name', 'hideable' => false],
            'sku' => 'Article ID',
            'price' => ['label' => 'Price', 'numeric' => true],
        ];
    }

    protected function query(): Builder
    {
        return D4Item::query();
    }

    protected function columnChooser(): bool
    {
        return true;
    }
}

class D4PlainTable extends DataTable
{
    protected function columns(): array
    {
        return ['name' => 'Name', 'sku' => 'Article ID', 'price' => 'Price'];
    }

    protected function query(): \Illuminate\Database\Query\Builder
    {
        return DB::table('d4_items');
    }
}
