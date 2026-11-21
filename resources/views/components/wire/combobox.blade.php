{{-- File: resources/views/components/wire/combobox.blade.php | Map: props -> x-oc::combobox (wire:ignore, Alpine scope) -> choice to the Livewire property -> server search through a component method
     OmniaUI combobox bound to a Livewire property. Needs oc-combobox.js (`npx omniaui copy-js combobox`).
     Props: model (Livewire property that gets the chosen value), live (send at once, like wire:model.live; default true),
     value-type ("number": the property gets a number, or null from the clear row; a custom row does not set it), search (name of a component method:
     it gets the typed text and returns a list of options [{ value, label, description?, icon?, image?, emoji? }]; the
     list is not filtered locally). Everything else (options, value, selected, label, placeholder, clear-label,
     manage-href, allow-custom-from-search, portal, variant, module ...) goes to x-oc::combobox. Pass :value with the
     current property value. wire:ignore keeps the script state between renders. --}}
@props(['model' => null, 'live' => true, 'valueType' => 'string', 'search' => null])
@php
    $value = $valueType === 'number'
        ? '($event.detail.value === \'\' ? null : Number($event.detail.value))'
        : '$event.detail.value';
    $wire = [
        'wire:ignore' => true,
        'x-data' => '',
    ];
    if ($model) {
        // Numeric ids: the custom row ("Use ...") does not set the property; listen to oc-combobox-change yourself for it.
        $wire['x-on:oc-combobox-change'] = ($valueType === 'number' ? '$event.detail.custom || ' : '').'$wire.set('.\Illuminate\Support\Js::from($model).', '.$value.', '.($live ? 'true' : 'false').')';
    }
    if ($search) {
        $wire['x-on:oc-combobox-search'] = '$wire.call('.\Illuminate\Support\Js::from($search).', $event.detail.query).then((items) => $el.ocSetOptions(items ?? [], $event.detail.query))';
    }
    // Kebab-case props (clear-label, manage-href ...) become camelCase so x-oc::combobox's @props picks them up.
    $pass = [];
    foreach ($attributes->getAttributes() as $key => $val) {
        $pass[preg_match('/^(data-|aria-|wire:|x-|@)/', $key) || ! str_contains($key, '-') ? $key : \Illuminate\Support\Str::camel($key)] = $val;
    }
    // A bag built here is printed as it is: escape the two Alpine expressions ourselves.
    $wire = array_map(fn ($v) => is_string($v) && $v !== '' ? e($v) : $v, $wire);
    $bag = new \Illuminate\View\ComponentAttributeBag(array_merge($pass, $wire));
@endphp
<x-oc::combobox :attributes="$bag" :value-type="$valueType" :search="$search ? 'event' : null" />
