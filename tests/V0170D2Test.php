<?php

// File: tests/V0170D2Test.php
// Map: wire/combobox (model -> $wire.set, number values, search method -> $wire.call + ocSetOptions, pass-through props)
//      -> wire/input mask + validation + ledger/module -> wire/select + wire/textarea ledger/module

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

use Illuminate\Support\Facades\Blade;

class V0170D2Test extends TestCase
{
    private function html(string $blade, array $data = []): string
    {
        return trim((string) preg_replace('/\s+/', ' ', Blade::render($blade, $data)));
    }

    public function test_wire_combobox_sets_the_property_and_searches_through_a_method(): void
    {
        $html = $this->html(
            '<x-oc::wire.combobox model="customerId" value-type="number" search="searchCustomers" label="Customer" clear-label="No customer" manage-href="/customers" portal variant="ledger" module="green" :value="$value" :selected="$selected" :options="$options" />',
            ['value' => 1, 'selected' => null, 'options' => [1 => ['label' => 'Company 1', 'description' => 'info@company1.example']]],
        );
        $this->assertStringContainsString('wire:ignore', $html);
        $this->assertStringContainsString('x-data=""', $html);
        $this->assertStringContainsString('data-oc-search="event"', $html);
        $this->assertStringContainsString('data-oc-value-type="number"', $html);
        $this->assertStringContainsString('x-on:oc-combobox-change="$event.detail.custom || $wire.set(&#039;customerId&#039;, ($event.detail.value === &#039;&#039; ? null : Number($event.detail.value)), true)"', $html);
        $this->assertStringContainsString('x-on:oc-combobox-search="$wire.call(&#039;searchCustomers&#039;, $event.detail.query).then((items) =&gt; $el.ocSetOptions(items ?? [], $event.detail.query))"', $html);
        // Pass-through props reach x-oc::combobox.
        $this->assertStringContainsString('class="oc-combobox oc-combobox--ledger"', $html);
        $this->assertStringContainsString('data-module="green"', $html);
        $this->assertStringContainsString('data-oc-portal', $html);
        $this->assertStringContainsString('oc-combobox-clear', $html);
        $this->assertStringContainsString('<a href="/customers" tabindex="-1">Manage</a>', $html);
        $this->assertStringContainsString('aria-label="Customer"', $html);
        $this->assertStringContainsString('value="Company 1"', $html);
        $this->assertStringContainsString('oc-combobox-option-description', $html);
    }

    public function test_wire_combobox_string_and_deferred(): void
    {
        $html = $this->html('<x-oc::wire.combobox model="country" :live="false" :options="[\'be\' => \'Belgium\']" />');
        $this->assertStringContainsString('$wire.set(&#039;country&#039;, $event.detail.value, false)', $html);
        $this->assertStringNotContainsString('data-oc-search', $html);
        $this->assertStringNotContainsString('oc-combobox-search', $html);
    }

    public function test_wire_input_mask_validation_ledger_module(): void
    {
        $html = $this->html('<x-oc::wire.input model="iban" mask="iban" validation-type="iban" validation-message="Not a valid IBAN" variant="ledger" module="blue" />');
        $this->assertStringContainsString('wire:model="iban"', $html);
        $this->assertStringContainsString('class="oc-input oc-input--ledger"', $html);
        $this->assertStringContainsString('data-oc-mask="iban"', $html);
        $this->assertStringContainsString('data-oc-validate="iban"', $html);
        $this->assertStringContainsString('data-oc-validation-message="Not a valid IBAN"', $html);
        $this->assertStringContainsString('data-module="blue"', $html);
        $this->assertStringNotContainsString('data-oc-', $this->html('<x-oc::wire.input model="name" />'));
    }

    public function test_wire_select_and_textarea_ledger_module(): void
    {
        $select = $this->html('<x-oc::wire.select model="country" variant="ledger" module="blue" :options="[\'be\' => \'Belgium\']" />');
        $this->assertStringContainsString('class="oc-select oc-select--ledger"', $select);
        $this->assertStringContainsString('data-module="blue"', $select);
        $textarea = $this->html('<x-oc::wire.textarea model="note" variant="ledger" />');
        $this->assertStringContainsString('class="oc-input oc-textarea--ledger"', $textarea);
        $this->assertStringNotContainsString('data-module', $textarea);
    }
}
