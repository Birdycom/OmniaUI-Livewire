{{-- OmniaCSS segmented control bound to a Livewire property: every radio gets the wire:model attribute.
     Props: model (required), options (value => label), label (aria-label), size (sm), block, live. --}}
@props(['model', 'options' => [], 'label' => null, 'size' => null, 'block' => false, 'live' => false])
@php($bound = \Omniacss\Livewire\WireModel::attributes($model, (bool) $live))
<div {{ $attributes->class(['oc-segmented', 'oc-segmented--sm' => $size === 'sm', 'oc-segmented--block' => $block])->merge(['role' => 'radiogroup', 'aria-label' => $label]) }}>
    @foreach ($options as $optionValue => $optionLabel)
        <label class="oc-segmented-item"><input type="radio" name="{{ $model }}" value="{{ $optionValue }}" {{ new \Illuminate\View\ComponentAttributeBag($bound) }}> {{ $optionLabel }}</label>
    @endforeach
</div>
