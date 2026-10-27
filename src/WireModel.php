<?php

declare(strict_types=1);

// File: src/WireModel.php | Map: shorthand props -> wire:model attribute

namespace Omniacss\Livewire;

/**
 * Turns the `model` shorthand of the wire components into a wire:model attribute.
 *
 *   WireModel::attributes('email', live: true, debounce: 300) => ['wire:model.live.debounce.300ms' => 'email']
 */
final class WireModel
{
    /**
     * @return array<string, string>
     */
    public static function attributes(?string $model, bool $live = false, bool $blur = false, ?int $debounce = null): array
    {
        if ($model === null || $model === '') {
            return [];
        }

        $name = 'wire:model';

        if ($live) {
            $name .= '.live';
        } elseif ($blur) {
            $name .= '.blur';
        }

        if ($debounce !== null) {
            $name .= $live ? ".debounce.{$debounce}ms" : '';
        }

        return [$name => $model];
    }

    /**
     * Livewire property name an explicit wire:model attribute points to (any modifier), or null.
     */
    public static function propertyOf(\Illuminate\View\ComponentAttributeBag $attributes): ?string
    {
        foreach ($attributes->whereStartsWith('wire:model')->getAttributes() as $value) {
            return is_string($value) ? $value : null;
        }

        return null;
    }
}
