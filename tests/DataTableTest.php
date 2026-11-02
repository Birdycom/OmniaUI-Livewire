<?php

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Omniacss\Livewire\DataTable;

class DataTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('people', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->decimal('balance', 8, 2)->default(0);
        });

        foreach (['Contact 1', 'Contact 2', 'Contact 3', 'Contact 4', 'Contact 5'] as $i => $name) {
            DB::table('people')->insert([
                'name' => $name,
                'email' => strtolower(explode(' ', $name)[0]).'@example.com',
                'balance' => ($i + 1) * 100,
            ]);
        }
    }

    public function test_renders_block_markup_with_rows(): void
    {
        Livewire::test(PeopleTable::class)
            ->assertSeeHtml('class="oc-card oc-block-datatable"')
            ->assertSeeHtml('class="oc-table-wrap"')
            ->assertSeeHtml('data-numeric')
            ->assertSee('Contact 1')
            ->assertSee('People')
            ->assertSee('Showing 1 to 5 of 5 entries');
    }

    public function test_search_filters_rows_and_resets_the_page(): void
    {
        Livewire::test(PeopleTable::class)
            ->set('search', 'contact 3')
            ->assertSee('Contact 3')
            ->assertDontSee('Contact 1')
            ->assertSee('Showing 1 to 1 of 1 entries');
    }

    public function test_empty_result_shows_the_empty_state(): void
    {
        Livewire::test(PeopleTable::class)
            ->set('search', 'zzz')
            ->assertSeeHtml('class="oc-table-empty"')
            ->assertSee('No results');
    }

    public function test_sorting_toggles_direction_and_sets_aria_sort(): void
    {
        Livewire::test(PeopleTable::class)
            ->call('sort', 'name')
            ->assertSet('sortBy', 'name')
            ->assertSet('sortDirection', 'asc')
            ->assertSeeHtml('aria-sort="ascending"')
            ->call('sort', 'name')
            ->assertSet('sortDirection', 'desc')
            ->assertSeeHtml('aria-sort="descending"')
            ->assertSeeInOrder(['Contact 5', 'Contact 4', 'Contact 3']);
    }

    public function test_unknown_or_unsortable_columns_are_ignored(): void
    {
        Livewire::test(PeopleTable::class)
            ->call('sort', 'password')
            ->assertSet('sortBy', '')
            ->call('sort', 'email')
            ->assertSet('sortBy', '');
    }

    public function test_pagination(): void
    {
        Livewire::test(PeopleTable::class)
            ->set('perPage', 2)
            ->assertSee('Showing 1 to 2 of 5 entries')
            ->assertSeeHtml('class="oc-pagination"')
            ->call('gotoPage', 2)
            ->assertSee('Contact 3')
            ->assertDontSee('Contact 1');
    }

    public function test_row_actions(): void
    {
        Livewire::test(PeopleTable::class)
            ->assertSeeHtml('class="oc-table-row-action"')
            ->assertSeeHtml('href="/people/1"')
            ->assertSeeHtml('class="oc-table-row-action oc-table-row-action--destructive"')
            ->assertSeeHtml('class="oc-btn oc-btn--soft-success oc-btn--icon oc-btn--sm" href="/people/1/view" aria-label="View" title="View"><iconify-icon icon="mdi:eye-outline"')
            ->assertSeeHtml('class="oc-btn oc-btn--soft-destructive oc-btn--icon oc-btn--sm" type="button" wire:click="archive(1)" aria-label="Archive"');
    }
}

class PeopleTable extends DataTable
{
    protected function columns(): array
    {
        return [
            'name' => 'Name',
            'email' => ['label' => 'E-mail', 'sortable' => false],
            'balance' => ['label' => 'Balance', 'numeric' => true],
        ];
    }

    protected function query(): Builder
    {
        return DB::table('people');
    }

    protected function title(): ?string
    {
        return 'People';
    }

    protected function actions(mixed $row): array
    {
        return [
            ['label' => 'Edit', 'href' => '/people/'.$row->id],
            ['label' => 'Delete', 'wire' => 'delete('.$row->id.')', 'destructive' => true],
            ['label' => 'View', 'href' => '/people/'.$row->id.'/view', 'icon' => 'mdi:eye-outline', 'tone' => 'success'],
            ['label' => 'Archive', 'wire' => 'archive('.$row->id.')', 'icon' => 'mdi:archive-outline', 'destructive' => true],
        ];
    }
}
