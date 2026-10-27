<?php

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

use Omniacss\Livewire\WireModel;

class ComponentsTest extends TestCase
{
    public function test_wire_model_shorthand(): void
    {
        $this->assertSame(['wire:model' => 'a'], WireModel::attributes('a'));
        $this->assertSame(['wire:model.live' => 'a'], WireModel::attributes('a', live: true));
        $this->assertSame(['wire:model.live.debounce.300ms' => 'a'], WireModel::attributes('a', live: true, debounce: 300));
        $this->assertSame(['wire:model.blur' => 'a'], WireModel::attributes('a', blur: true));
        $this->assertSame([], WireModel::attributes(null));
    }

    public function test_input_binds_with_shorthand_and_keeps_oc_classes(): void
    {
        $html = $this->render('<x-oc::wire.input model="email" live :debounce="300" type="email" />');

        $this->assertStringContainsString('class="oc-input"', $html);
        $this->assertStringContainsString('wire:model.live.debounce.300ms="email"', $html);
        $this->assertStringContainsString('type="email"', $html);
    }

    public function test_input_passes_an_explicit_wire_model_attribute_through(): void
    {
        $html = $this->render('<x-oc::wire.input wire:model.blur="name" />');

        $this->assertStringContainsString('wire:model.blur="name"', $html);
    }

    public function test_input_flags_validation_errors_of_the_bound_property(): void
    {
        $errors = (new \Illuminate\Support\ViewErrorBag)->put('default', new \Illuminate\Support\MessageBag(['email' => ['Required']]));

        view()->share('errors', $errors);

        $html = $this->render('<x-oc::wire.input model="email" />');

        $this->assertStringContainsString('oc-input oc-input--error', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
    }

    public function test_select_switch_and_checkbox_pass_wire_model(): void
    {
        $select = $this->render('<x-oc::wire.select model="country" :options="[\'be\' => \'Belgium\']" />');
        $this->assertStringContainsString('class="oc-select"', $select);
        $this->assertStringContainsString('wire:model="country"', $select);
        $this->assertStringContainsString('<option value="be">Belgium</option>', $select);

        $switch = $this->render('<x-oc::wire.switch model="active" live />');
        $this->assertStringContainsString('class="oc-switch"', $switch);
        $this->assertStringContainsString('role="switch"', $switch);
        $this->assertStringContainsString('wire:model.live="active"', $switch);

        $checkbox = $this->render('<x-oc::wire.checkbox model="tags" value="a" label="A" />');
        $this->assertStringContainsString('class="oc-checkbox"', $checkbox);
        $this->assertStringContainsString('wire:model="tags"', $checkbox);
    }

    public function test_segmented_binds_every_radio(): void
    {
        $html = $this->render('<x-oc::wire.segmented model="period" :options="[\'m\' => \'Month\', \'y\' => \'Year\']" />');

        $this->assertStringContainsString('class="oc-segmented"', $html);
        $this->assertSame(2, substr_count($html, 'wire:model="period"'));
        $this->assertSame(2, substr_count($html, 'type="radio"'));
    }

    public function test_otp_input_completion_hook(): void
    {
        $html = $this->render('<x-oc::wire.otp model="code" complete="verify" :length="6" />');

        $this->assertStringContainsString('oc-otp', $html);
        $this->assertStringContainsString('wire:model="code"', $html);
        $this->assertStringContainsString('--oc-otp-length: 6', $html);
        $this->assertStringContainsString('$wire.verify()', $html);
    }

    public function test_tag_input_entangles_the_array_property(): void
    {
        $html = $this->render('<x-oc::wire.tag-input model="tags" :suggestions="[\'design\']" />');

        $this->assertStringContainsString('$wire.entangle(', $html);
        $this->assertStringContainsString('oc-tag-input', $html);
        $this->assertStringContainsString('class="oc-tag-remove"', $html);
        $this->assertStringContainsString('oc-tag-suggestions', $html);
    }

    public function test_modal_sheet_and_alert_dialog_are_entangled(): void
    {
        $modal = $this->render('<x-oc::wire.modal name="confirm" title="Sure?" size="sm">Text</x-oc::wire.modal>');
        $this->assertStringContainsString('class="oc-modal"', $modal);
        $this->assertStringContainsString('ocModals.confirm', $modal);
        $this->assertStringContainsString('showModal()', $modal);
        $this->assertStringContainsString('oc-modal-content oc-modal-content--sm', $modal);

        $sheet = $this->render('<x-oc::wire.sheet model="showFilters" side="left" title="Filters">Body</x-oc::wire.sheet>');
        $this->assertStringContainsString('class="oc-sheet oc-sheet--left"', $sheet);
        $this->assertStringContainsString('showFilters', $sheet);

        $alert = $this->render('<x-oc::wire.alert-dialog name="delete" type="error" title="Delete?" action="delete(5)" confirm="Delete">Gone</x-oc::wire.alert-dialog>');
        $this->assertStringContainsString('oc-alert-dialog oc-alert-dialog--error', $alert);
        $this->assertStringContainsString('wire:click="delete(5)"', $alert);
    }

    public function test_toast_stack_renders_container_and_listener_once(): void
    {
        $html = $this->render('<x-oc::toast-stack top /><x-oc::toast-stack />');

        $this->assertStringContainsString('oc-toast-container oc-toast-container--top', $html);
        $this->assertSame(1, substr_count($html, "addEventListener('oc-toast'"));
    }

    public function test_base_laravel_components_stay_available(): void
    {
        $this->assertStringContainsString('class="oc-btn oc-btn--outline"', $this->render('<x-oc::button variant="outline">Go</x-oc::button>'));
    }
}
