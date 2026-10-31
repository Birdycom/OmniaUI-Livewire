<!-- File: php-packages/omniaui-livewire/README.md | Map: purpose -> install -> components -> modals -> toasts -> data table -> tests -> notes -->
# birdycom/omniaui-livewire

Livewire 3 and 4 versions of the OmniaUI components where state matters. Builds on [`birdycom/omniaui-laravel`](https://packagist.org/packages/birdycom/omniaui-laravel) (same `oc` prefix, so `<x-oc::wire.modal>`). Everything is Blade plus a little Alpine (shipped with Livewire); there are no colors in PHP.

- Version: `0.12.4` (same version as OmniaCSS and OmniaUI)
- Site: https://omniaui.eu · Source: https://github.com/Birdycom/OmniaUI (`php-packages/omniaui-livewire`)

## Install

```bash
composer require birdycom/omniaui-livewire     # also installs birdycom/omniaui-laravel
php artisan omniacss:install
```

## Components

| Component | Props | Purpose |
|-----------|-------|---------|
| `<x-oc::wire.input>` | `model`, `live`, `blur`, `debounce`, `error` | Input with a `wire:model` shorthand; a validation error on `model` adds `oc-input--error` automatically |
| `<x-oc::wire.textarea>` | `model`, `live`, `blur`, `debounce`, `rows`, `error` | The same for a textarea |
| `<x-oc::wire.select>` | `model`, `live`, `options`, `placeholder`, `error` | Select |
| `<x-oc::wire.switch>` | `model`, `live` | Switch (attributes go to the checkbox) |
| `<x-oc::wire.checkbox>` | `model`, `live`, `radio`, `label` | Checkbox or radio, also as a checkbox group bound to an array |
| `<x-oc::wire.segmented>` | `model`, `options`, `label`, `size`, `block`, `live` | `wire:model` on every radio |
| `<x-oc::wire.otp>` | `model`, `length`, `live`, `complete`, `label`, `description`, `error` | One-time code; `complete="verify"` calls `verify()` as soon as the code is full |
| `<x-oc::wire.tag-input>` | `model`, `live`, `placeholder`, `suggestions`, `label` | Tags in an array property through `$wire.entangle` (Enter or comma adds one) |
| `<x-oc::wire.modal>` | `name` or `model`, `title`, `description`, `size`, `closedby` | Modal on `<dialog>`, open state bound to Livewire |
| `<x-oc::wire.sheet>` | `name` or `model`, `side`, `wide`, `title`, `description` | Side panel, same binding |
| `<x-oc::wire.alert-dialog>` | `name` or `model`, `type`, `title`, `confirm`, `cancel`, `action` | Confirmation; `action="delete(5)"` runs on confirm |
| `<x-oc::toast-stack>` | `top`, `timeout` | Listens for the browser event `oc-toast`; put it once in the layout |

You can also pass `wire:model` as a plain attribute (`<x-oc::wire.input wire:model.live="email" />`); the `model` shorthand is a convenience.

```blade
<x-oc::wire.input model="email" live :debounce="300" type="email" />
<x-oc::wire.segmented model="period" :options="['month' => 'Month', 'year' => 'Year']" />
<x-oc::wire.tag-input model="tags" :suggestions="['customer', 'supplier']" />
```

## Modals: `WithOcModal`

```php
use Omniacss\Livewire\WithOcModal;

class Contacts extends Component
{
    use WithOcModal;   // public array $ocModals + openOcModal / closeOcModal / toggleOcModal / isOcModalOpen

    public function delete(int $id): void { /* ... */ $this->closeOcModal('delete'); }
}
```

```blade
<button class="oc-btn" wire:click="openOcModal('delete')">Delete</button>

<x-oc::wire.alert-dialog name="delete" title="Delete contact?" confirm="Delete" action="delete({{ $contact->id }})">
    This cannot be undone.
</x-oc::wire.alert-dialog>
```

Without the trait, bind your own boolean: `<x-oc::wire.modal model="showFilters">`. Esc and `closedby` set the property back to `false`.

## Toasts: `WithOcToast`

```php
use Omniacss\Livewire\WithOcToast;

class Contacts extends Component
{
    use WithOcToast;

    public function save(): void
    {
        // ...
        $this->ocToast('success', 'Saved', 'Your changes are stored.');
    }
}
```

```blade
<x-oc::toast-stack />   {{-- once in your layout --}}
```

`ocToast(type, title, description = null, timeout = null)` dispatches the `oc-toast` event. Outside Livewire: `window.dispatchEvent(new CustomEvent('oc-toast', { detail: { type: 'info', title: 'Hello' } }))`.

## DataTable

```php
use Omniacss\Livewire\DataTable;

class UsersTable extends DataTable
{
    protected function columns(): array
    {
        return [
            'name' => 'Name',
            'email' => ['label' => 'Email', 'sortable' => false],
            'balance' => ['label' => 'Balance', 'numeric' => true],
        ];
    }

    protected function query(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query();
    }

    protected function title(): ?string { return 'Users'; }

    protected function actions(mixed $row): array
    {
        return [
            ['label' => 'Edit', 'href' => route('users.edit', $row)],
            ['label' => 'Delete', 'wire' => "delete({$row->id})", 'destructive' => true],
        ];
    }
}
```

```blade
<livewire:users-table />
```

Public properties (also in the URL): `search` (`q`), `sortBy` (`sort`), `sortDirection` (`dir`), `perPage` (`per`). Hooks: `columns()`, `query()`, `title()`, `description()`, `actions($row)`, `cell($key, $row)`, `rows()`. The markup is the `data-table` block.

## Tests

```bash
composer install     # in the monorepo this uses ../omniaui-laravel as a path repository
vendor/bin/phpunit
```

## Notes

- The dialog components use `wire:ignore.self` and Alpine (`$wire.entangle`), shipped with Livewire 3 and 4.
- `wire.tag-input` is `wire:ignore`; its state flows through the entangled array.
- This package is developed in the [OmniaUI monorepo](https://github.com/Birdycom/OmniaUI) and mirrored to its own repository for Packagist. Open issues and pull requests there.

## License

MIT — Serge Cools (Birdycom).
