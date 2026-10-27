{{-- OmniaCSS modal on <dialog>, open state entangled with a Livewire property (Alpine $wire.entangle).
     Props: name (uses `ocModals.{name}` from the WithOcModal trait) OR model (any boolean property), title, description, size, closedby, id.
     Slots: default = body, footer. Open it with wire:click="openOcModal('name')" or by setting the property; Esc / close buttons set it back to false. --}}
@props(['name' => 'default', 'model' => null, 'title' => null, 'description' => null, 'size' => null, 'closedby' => 'any', 'id' => null])
@php
    $property = $model ?: 'ocModals.'.$name;
    $id = $id ?: 'oc-modal-'.substr(md5($property), 0, 8);
@endphp
<dialog wire:ignore.self
        x-data="{ open: $wire.entangle({{ \Illuminate\Support\Js::from($property) }}) }"
        x-effect="open ? ($el.open || $el.showModal()) : ($el.open && $el.close())"
        x-on:close="open = false"
        {{ $attributes->class(['oc-modal'])->merge(['id' => $id, 'closedby' => $closedby, 'aria-labelledby' => $id.'-title']) }}>
    <div @class(['oc-modal-content', 'oc-modal-content--'.$size => $size])>
        @if ($title || $description)
            <div class="oc-modal-header">
                @if ($title)
                    <h2 class="oc-modal-title" id="{{ $id }}-title">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="oc-modal-description">{{ $description }}</p>
                @endif
            </div>
        @endif
        @if (! $slot->isEmpty())
            <div class="oc-modal-body">{{ $slot }}</div>
        @endif
        @if (isset($footer) && trim((string) $footer) !== '')
            <div class="oc-modal-footer">{{ $footer }}</div>
        @endif
    </div>
</dialog>
