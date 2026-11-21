{{-- OmniaCSS textarea with wire:model shorthand. Props: model, live, blur, debounce, rows, error.
     0.17.0: variant ("ledger"), module (data-module). --}}
@props(['model' => null, 'live' => false, 'blur' => false, 'debounce' => null, 'rows' => 4, 'error' => null, 'variant' => null, 'module' => null])
@php
    $property = $model ?? \Omniacss\Livewire\WireModel::propertyOf($attributes);
    $invalid = $error ?? ($property && isset($errors) && $errors->has($property));
@endphp
<textarea {{ $attributes->class(['oc-input', 'oc-input--error' => $invalid, 'oc-textarea--ledger' => $variant === 'ledger'])->merge(['rows' => $rows])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live, (bool) $blur, $debounce))->merge(array_filter(['data-module' => $module], fn ($v) => $v !== null)) }} @if ($invalid) aria-invalid="true" @endif></textarea>
