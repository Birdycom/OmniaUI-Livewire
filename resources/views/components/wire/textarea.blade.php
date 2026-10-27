{{-- OmniaCSS textarea with wire:model shorthand. Props: model, live, blur, debounce, rows, error. --}}
@props(['model' => null, 'live' => false, 'blur' => false, 'debounce' => null, 'rows' => 4, 'error' => null])
@php
    $property = $model ?? \Omniacss\Livewire\WireModel::propertyOf($attributes);
    $invalid = $error ?? ($property && isset($errors) && $errors->has($property));
@endphp
<textarea {{ $attributes->class(['oc-input', 'oc-input--error' => $invalid])->merge(['rows' => $rows])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live, (bool) $blur, $debounce)) }} @if ($invalid) aria-invalid="true" @endif></textarea>
