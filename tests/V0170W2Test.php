<?php

// File: tests/V0170W2Test.php
// Map: named slots close (sidebar footer/icon) -> wire modal close-round -> wire alert dialog discard -> data table variant + labels
// Livewire hooks of 0.17.0 (package W2).

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Omniacss\Livewire\DataTable;

class V0170W2Test extends TestCase
{
    private function html(string $blade): string
    {
        return trim((string) preg_replace('/\s+/', ' ', Blade::render($blade)));
    }

    public function test_named_slots_followed_by_text_close_their_buffer(): void
    {
        $level = ob_get_level();
        $html = $this->html('<x-oc::sidebar><x-oc::sidebar.item href="/"><x-slot:icon><span class="i"></span></x-slot:icon>Dashboard</x-oc::sidebar.item><x-slot:footer>Foot</x-slot:footer></x-oc::sidebar>');
        $this->assertSame($level, ob_get_level());
        $this->assertStringContainsString('Dashboard', $html);
        $this->assertStringNotContainsString('@endslot', $html);
    }

    public function test_wire_modal_close_round(): void
    {
        $html = $this->html('<x-oc::wire.modal name="edit" title="Edit" close-round close="Close dialog">Body</x-oc::wire.modal>');
        $this->assertStringContainsString('<button class="oc-modal-close oc-close--round" type="button" x-on:click="open = false" aria-label="Close dialog">&times;</button>', $html);
        $this->assertStringNotContainsString('oc-modal-close', $this->html('<x-oc::wire.modal name="edit">Body</x-oc::wire.modal>'));
    }

    public function test_wire_alert_dialog_discard(): void
    {
        $html = $this->html('<x-oc::wire.alert-dialog name="unsaved" title="Save?" confirm="Save" action="save" discard="Don\'t save" discard-action="leave">Changes</x-oc::wire.alert-dialog>');
        $this->assertStringContainsString('<button class="oc-btn oc-btn--ghost oc-alert-dialog-discard" type="button" wire:click="leave" x-on:click="open = false">Don&#039;t save</button>', $html);
        $this->assertLessThan(strpos($html, 'oc-btn oc-btn--outline'), strpos($html, 'oc-alert-dialog-discard'));
    }

    public function test_data_table_variant_and_labels(): void
    {
        Schema::create('w2_people', function ($table): void {
            $table->id();
            $table->string('name');
        });
        DB::table('w2_people')->insert(['name' => 'Contact 1']);

        Livewire::test(W2PeopleTable::class)
            ->assertSeeHtml('class="oc-table oc-table--striped oc-table--compact"')
            ->assertSeeHtml('<span class="oc-visually-hidden">Acties</span>')
            ->assertSee('1 tot 1 van 1')
            ->assertSee('Rijen per pagina')
            ->set('search', 'nobody')
            ->assertSee('Geen resultaten')
            ->assertSee('Niets gevonden voor nobody');
    }
}

class W2PeopleTable extends DataTable
{
    protected function columns(): array
    {
        return ['name' => 'Name'];
    }

    protected function query(): Builder
    {
        return DB::table('w2_people');
    }

    protected function variant(): string|array
    {
        return ['striped', 'compact'];
    }

    protected function labels(): array
    {
        return ['actions' => 'Acties', 'showing' => '{from} tot {to} van {total}', 'rowsPerPage' => 'Rijen per pagina', 'noResults' => 'Geen resultaten', 'nothingMatches' => 'Niets gevonden voor {search}'];
    }
}
