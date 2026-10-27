<?php

declare(strict_types=1);

// File: src/WithOcModal.php | Map: state -> open/close/toggle

namespace Omniacss\Livewire;

/**
 * Server-side control of <x-oc::wire.modal name="...">, <x-oc::wire.sheet name="..."> and <x-oc::wire.alert-dialog name="...">.
 *
 *   class Contacts extends Component {
 *       use \Omniacss\Livewire\WithOcModal;
 *       public function confirmDelete() { $this->openOcModal('delete'); }
 *   }
 *
 * The components entangle `ocModals.{name}`, so wire:click="openOcModal('delete')" works from the view as well.
 */
trait WithOcModal
{
    /** @var array<string, bool> */
    public array $ocModals = [];

    public function openOcModal(string $name = 'default'): void
    {
        $this->ocModals[$name] = true;
    }

    public function closeOcModal(string $name = 'default'): void
    {
        $this->ocModals[$name] = false;
    }

    public function toggleOcModal(string $name = 'default'): void
    {
        $this->ocModals[$name] = ! ($this->ocModals[$name] ?? false);
    }

    public function isOcModalOpen(string $name = 'default'): bool
    {
        return (bool) ($this->ocModals[$name] ?? false);
    }
}
