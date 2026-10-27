{{-- OmniaCSS input with wire:model shorthand. Props: model (Livewire property), live, blur, debounce (ms), error.
     An explicit wire:model.* attribute works as well and is passed through. Errors are read from the validator for `model`. --}}
@props(['model' => null, 'live' => false, 'blur' => false, 'debounce' => null, 'error' => null])
@php
    $property = $model ?? \Omniacss\Livewire\WireModel::propertyOf($attributes);
    $invalid = $error ?? ($property && isset($errors) && $errors->has($property));
@endphp
<input {{ $attributes->class(['oc-input', 'oc-input--error' => $invalid])->merge(['type' => 'text'])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live, (bool) $blur, $debounce)) }} @if ($invalid) aria-invalid="true" @endif>
