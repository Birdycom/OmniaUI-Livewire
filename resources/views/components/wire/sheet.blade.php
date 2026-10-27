{{-- OmniaCSS sheet (side panel) with its open state entangled to a Livewire property. Props: name | model, side (right|left|top|bottom), wide, title, description, closedby, close, id.
     Slots: default = body, footer. --}}
@props(['name' => 'default', 'model' => null, 'side' => 'right', 'wide' => false, 'title' => null, 'description' => null, 'closedby' => 'any', 'close' => 'Close', 'id' => null])
@php
    $property = $model ?: 'ocModals.'.$name;
    $id = $id ?: 'oc-sheet-'.substr(md5($property), 0, 8);
@endphp
<dialog wire:ignore.self
        x-data="{ open: $wire.entangle({{ \Illuminate\Support\Js::from($property) }}) }"
        x-effect="open ? ($el.open || $el.showModal()) : ($el.open && $el.close())"
        x-on:close="open = false"
        {{ $attributes->class(['oc-sheet', 'oc-sheet--'.$side => $side && $side !== 'right'])->merge(['id' => $id, 'closedby' => $closedby, 'aria-labelledby' => $id.'-title']) }}>
    <div @class(['oc-sheet-content', 'oc-sheet-content--wide' => $wide])>
        <button class="oc-sheet-close" type="button" x-on:click="open = false" aria-label="{{ $close }}">&times;</button>
        @if ($title || $description)
            <div class="oc-sheet-header">
                @if ($title)
                    <h2 class="oc-sheet-title" id="{{ $id }}-title">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="oc-sheet-description">{{ $description }}</p>
                @endif
            </div>
        @endif
        <div class="oc-sheet-body">{{ $slot }}</div>
        @if (isset($footer) && trim((string) $footer) !== '')
            <div class="oc-sheet-footer">{{ $footer }}</div>
        @endif
    </div>
</dialog>
