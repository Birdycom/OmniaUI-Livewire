{{-- OmniaCSS select with wire:model shorthand. Props: model, live, blur, options (value => label), placeholder, error. Slot: raw <option> markup. --}}
@props(['model' => null, 'live' => false, 'blur' => false, 'options' => [], 'placeholder' => null, 'error' => null])
@php
    $property = $model ?? \Omniacss\Livewire\WireModel::propertyOf($attributes);
    $invalid = $error ?? ($property && isset($errors) && $errors->has($property));
@endphp
<select {{ $attributes->class(['oc-select'])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live, (bool) $blur)) }} @if ($invalid) aria-invalid="true" @endif>
    @if ($placeholder !== null)
        <option value="">{{ $placeholder }}</option>
    @endif
    @foreach ($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}">{{ $optionLabel }}</option>
    @endforeach
    {{ $slot }}
</select>
