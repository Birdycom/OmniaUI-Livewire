{{-- OmniaCSS alert dialog (confirmation) with its open state entangled to a Livewire property.
     Props: name | model, type (success|error|warning|info|question), title, icon, cancel, confirm (labels), confirmVariant,
            action (Livewire expression run on confirm, e.g. "delete(5)"), closedby, id. Slot: message. --}}
@props(['name' => 'default', 'model' => null, 'type' => 'warning', 'title' => null, 'icon' => null, 'cancel' => 'Cancel', 'confirm' => 'Confirm', 'confirmVariant' => 'destructive', 'action' => null, 'closedby' => 'closerequest', 'id' => null])
@php
    $property = $model ?: 'ocModals.'.$name;
    $id = $id ?: 'oc-alert-dialog-'.substr(md5($property), 0, 8);
    $glyph = $icon ?? match ($type) {
        'success' => '✓',
        'error' => '×',
        'question' => '?',
        'info' => 'i',
        default => '!',
    };
    $confirmClass = in_array($confirmVariant, ['primary', 'default'], true) ? 'oc-btn' : 'oc-btn oc-btn--'.$confirmVariant;
@endphp
<dialog wire:ignore.self
        x-data="{ open: $wire.entangle({{ \Illuminate\Support\Js::from($property) }}) }"
        x-effect="open ? ($el.open || $el.showModal()) : ($el.open && $el.close())"
        x-on:close="open = false"
        {{ $attributes->class(['oc-modal'])->merge(['id' => $id, 'closedby' => $closedby, 'aria-labelledby' => $id.'-title', 'aria-describedby' => $id.'-text']) }}>
    <div class="oc-modal-content oc-modal-content--sm oc-alert-dialog oc-alert-dialog--{{ $type }}">
        <span class="oc-alert-dialog-icon" aria-hidden="true">{{ $glyph }}</span>
        <h2 class="oc-modal-title" id="{{ $id }}-title">{{ $title }}</h2>
        <div class="oc-modal-body" id="{{ $id }}-text">{{ $slot }}</div>
        <div class="oc-modal-footer">
            <button class="oc-btn oc-btn--outline" type="button" x-on:click="open = false">{{ $cancel }}</button>
            <button class="{{ $confirmClass }}" type="button" @if ($action) wire:click="{{ $action }}" @endif x-on:click="open = false">{{ $confirm }}</button>
        </div>
    </div>
</dialog>
