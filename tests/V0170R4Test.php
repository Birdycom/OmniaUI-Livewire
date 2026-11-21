<?php

// File: tests/V0170R4Test.php
// Map: 0.17.0 round 4: Livewire DataTable column 'hideBelow' (or 'hide-below') -> data-hide-below on th, tds and skeleton tds;
//      unknown values are dropped

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Omniacss\Livewire\DataTable;

class V0170R4Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('contacts_r4', function ($table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('status');
        });

        DB::table('contacts_r4')->insert([
            ['name' => 'Contact 1', 'email' => 'info@contact1.example', 'status' => 'Active'],
            ['name' => 'Contact 2', 'email' => 'info@contact2.example', 'status' => 'Pending'],
        ]);
    }

    public function test_hide_below_on_header_cells_and_skeleton(): void
    {
        $html = Livewire::test(HideBelowTable::class)->html();

        // th + 2 rows + skeleton rows for e-mail (lg) and status (sm)
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'data-hide-below="lg"'));
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'data-hide-below="sm"'));
        $this->assertSame(substr_count($html, 'data-hide-below="lg"'), substr_count($html, 'data-hide-below="sm"'));
        $this->assertStringNotContainsString('data-hide-below="xl"', $html);
        $this->assertMatchesRegularExpression('/<th[^>]*data-hide-below="lg"[^>]*>(?:(?!<\/th>).)*E-mail<\/a>/s', $html);
        $this->assertMatchesRegularExpression('/<td[^>]*data-hide-below="lg"[^>]*>info@contact1\.example<\/td>/', $html);
    }
}

class HideBelowTable extends DataTable
{
    protected function columns(): array
    {
        return [
            'name' => 'Name',
            'email' => ['label' => 'E-mail', 'hideBelow' => 'lg'],
            'status' => ['label' => 'Status', 'hide-below' => 'sm', 'searchable' => false],
            'id' => ['label' => 'ID', 'hideBelow' => 'xl', 'numeric' => true],
        ];
    }

    protected function query(): Builder
    {
        return DB::table('contacts_r4');
    }
}
