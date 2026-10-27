<?php

declare(strict_types=1);

namespace Omniacss\Livewire\Tests;

use Livewire\Component;
use Livewire\Livewire;
use Omniacss\Livewire\WithOcModal;
use Omniacss\Livewire\WithOcToast;

class ComponentsAndTraitsTest extends TestCase
{
    public function test_toast_helper_dispatches_the_browser_event(): void
    {
        Livewire::test(ToastComponent::class)
            ->call('save')
            ->assertDispatched('oc-toast', type: 'success', title: 'Saved');
    }

    public function test_modal_trait_controls_state(): void
    {
        Livewire::test(ModalComponent::class)
            ->assertSet('ocModals', [])
            ->call('openOcModal', 'confirm')
            ->assertSet('ocModals.confirm', true)
            ->call('toggleOcModal', 'confirm')
            ->assertSet('ocModals.confirm', false)
            ->call('openOcModal')
            ->assertSet('ocModals.default', true)
            ->call('closeOcModal');
    }

    public function test_component_view_can_use_the_wire_components(): void
    {
        Livewire::test(ModalComponent::class)
            ->assertSeeHtml('wire:model="name"')
            ->assertSeeHtml('class="oc-modal"');
    }
}

class ToastComponent extends Component
{
    use WithOcToast;

    public function save(): void
    {
        $this->ocToast('success', 'Saved');
    }

    public function render(): string
    {
        return '<div><x-oc::toast-stack /></div>';
    }
}

class ModalComponent extends Component
{
    use WithOcModal;

    public string $name = '';

    public function render(): string
    {
        return <<<'BLADE'
        <div>
            <x-oc::wire.input model="name" />
            <x-oc::wire.modal name="confirm" title="Sure?">Text</x-oc::wire.modal>
        </div>
        BLADE;
    }
}
