{{-- OmniaCSS input with wire:model shorthand. Props: model (Livewire property), live, blur, debounce (ms), error.
     An explicit wire:model.* attribute works as well and is passed through. Errors are read from the validator for `model`.
     0.17.0: variant ("ledger"), module (data-module), mask, validation-type, validation-message (oc-input-mask.js;
     `npx omniaui copy-js input-mask`). --}}
@props(['model' => null, 'live' => false, 'blur' => false, 'debounce' => null, 'error' => null, 'variant' => null, 'module' => null, 'mask' => null, 'validationType' => null, 'validationMessage' => null])
@php
    $property = $model ?? \Omniacss\Livewire\WireModel::propertyOf($attributes);
    $invalid = $error ?? ($property && isset($errors) && $errors->has($property));
    $data = array_filter([
        'data-module' => $module,
        'data-oc-mask' => $mask,
        'data-oc-validate' => $validationType,
        'data-oc-validation-message' => $validationMessage,
    ], fn ($v) => $v !== null && $v !== '');
@endphp
<input {{ $attributes->class(['oc-input', 'oc-input--error' => $invalid, 'oc-input--ledger' => $variant === 'ledger'])->merge(['type' => 'text'])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live, (bool) $blur, $debounce))->merge($data) }} @if ($invalid) aria-invalid="true" @endif>
