{{-- OmniaCSS one-time-code input bound to a Livewire property. Props: model, length (default 6), live, complete (Livewire method called when the code is complete, e.g. "verify"; called on the next tick, after wire:model
     has taken the new value),
     label, description, id, error. --}}
@props(['model' => null, 'length' => 6, 'live' => false, 'complete' => null, 'label' => null, 'description' => null, 'id' => null, 'error' => null])
@php
    $id = $id ?: 'oc-otp-'.substr(md5((string) $model.uniqid('', true)), 0, 6);
    $property = $model ?? \Omniacss\Livewire\WireModel::propertyOf($attributes);
    $invalid = $error ?? ($property && isset($errors) && $errors->has($property));
@endphp
<div class="oc-field">
    @if ($label)
        <label class="oc-field-label" for="{{ $id }}">{{ $label }}</label>
    @endif
    <input {{ $attributes->class(['oc-otp'])->merge(['id' => $id, 'type' => 'text', 'inputmode' => 'numeric', 'autocomplete' => 'one-time-code', 'maxlength' => $length, 'pattern' => '[0-9]*', 'style' => '--oc-otp-length: '.(int) $length])->merge(\Omniacss\Livewire\WireModel::attributes($model, (bool) $live)) }}
        @if ($complete) x-data x-on:input="if ($event.target.value.length >= {{ (int) $length }}) $nextTick(() => $wire.{{ $complete }}())" @endif
        @if ($invalid) aria-invalid="true" @endif>
    @if ($description)
        <p class="oc-field-description">{{ $description }}</p>
    @endif
</div>
