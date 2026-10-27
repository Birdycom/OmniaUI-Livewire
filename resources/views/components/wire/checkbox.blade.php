{{-- OmniaCSS checkbox / radio with wire:model shorthand. Props: model, live, radio, label. With a `value` attribute and an array property it works as a checkbox group. --}}
@props(['model' => null, 'live' => false, 'radio' => false, 'label' => null])
<label @if ($attributes->has('class')) class="{{ $attributes->get('class') }}" @endif>
    <input {{ $attributes->except('class')->merge(['class' => $radio ? 'oc-radio' : 'oc-checkbox', 'type' => $radio ? 'radio' : 'checkbox'])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live)) }}>
    {{ $label }}{{ $slot }}
</label>
