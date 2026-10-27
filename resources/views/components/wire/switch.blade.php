{{-- OmniaCSS switch with wire:model shorthand. Props: model, live. `class` goes to the label, everything else to the checkbox. --}}
@props(['model' => null, 'live' => false])
<label @if ($attributes->has('class')) class="oc-switch {{ $attributes->get('class') }}" @else class="oc-switch" @endif>
    <input {{ $attributes->except('class')->merge(['type' => 'checkbox', 'role' => 'switch'])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live)) }}>
    <span class="oc-switch-track"></span>
</label>
