<!-- File: php-packages/omniaui-livewire/README.md | Map: purpose -> install -> components -> traits -> data table -> tests -->
# birdycom/omniaui-livewire

## Version

- Version: `0.4.0`
- Status: `Beta`

---

## Purpose

Livewire 3-varianten van OmniaCSS-componenten waar state ertoe doet. Bouwt op `birdycom/omniaui-laravel` (zelfde `oc`-prefix, dus `<x-oc::wire.modal>`). Alles is Blade + een beetje Alpine (zit in Livewire 3); er staan geen kleuren in PHP.

## Install

```bash
composer require birdycom/omniaui-livewire     # haalt birdycom/omniaui-laravel mee
php artisan omniacss:install
```

## Components

| Component | Props | Doel |
|-----------|-------|------|
| `<x-oc::wire.input>` | `model`, `live`, `blur`, `debounce`, `error` | Input met `wire:model` shorthand; validatiefout van `model` wordt automatisch `oc-input--error` |
| `<x-oc::wire.textarea>` | `model`, `live`, `blur`, `debounce`, `rows`, `error` | Idem voor een textarea |
| `<x-oc::wire.select>` | `model`, `live`, `options`, `placeholder`, `error` | Select |
| `<x-oc::wire.switch>` | `model`, `live` | Switch (attributen naar de checkbox) |
| `<x-oc::wire.checkbox>` | `model`, `live`, `radio`, `label` | Checkbox of radio, ook als checkbox-groep met een array |
| `<x-oc::wire.segmented>` | `model`, `options`, `label`, `size`, `block`, `live` | `wire:model` op elke radio |
| `<x-oc::wire.otp>` | `model`, `length`, `live`, `complete`, `label`, `description`, `error` | Eenmalige code; `complete="verify"` roept `verify()` aan zodra de code vol is |
| `<x-oc::wire.tag-input>` | `model`, `live`, `placeholder`, `suggestions`, `label` | Tags in een array-property via `$wire.entangle` (Enter of komma voegt toe) |
| `<x-oc::wire.modal>` | `name` of `model`, `title`, `description`, `size`, `closedby` | Modal op `<dialog>`, open-state gekoppeld aan Livewire |
| `<x-oc::wire.sheet>` | `name` of `model`, `side`, `wide`, `title`, `description` | Zijpaneel, idem |
| `<x-oc::wire.alert-dialog>` | `name` of `model`, `type`, `title`, `confirm`, `cancel`, `action` | Bevestiging; `action="delete(5)"` wordt uitgevoerd bij bevestigen |
| `<x-oc::toast-stack>` | `top`, `timeout` | Luistert naar het browser-event `oc-toast`; eenmaal in de layout |

`wire:model` kun je ook gewoon als attribuut meegeven (`<x-oc::wire.input wire:model.live="email" />`); de `model`-shorthand is voor gemak.

```blade
<x-oc::wire.input model="email" live :debounce="300" type="email" />
<x-oc::wire.segmented model="period" :options="['month' => 'Maand', 'year' => 'Jaar']" />
<x-oc::wire.tag-input model="tags" :suggestions="['klant', 'leverancier']" />
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
<button class="oc-btn" wire:click="openOcModal('delete')">Verwijderen</button>

<x-oc::wire.alert-dialog name="delete" title="Contact verwijderen?" confirm="Verwijderen" action="delete({{ $contact->id }})">
    Dit kan niet ongedaan worden gemaakt.
</x-oc::wire.alert-dialog>
```

Zonder trait koppel je met een eigen boolean: `<x-oc::wire.modal model="showFilters">`. Esc en `closedby` zetten de property weer op `false`.

## Toasts: `WithOcToast`

```php
use Omniacss\Livewire\WithOcToast;

class Contacts extends Component
{
    use WithOcToast;

    public function save(): void
    {
        // ...
        $this->ocToast('success', 'Opgeslagen', 'De wijzigingen zijn bewaard.');
    }
}
```

```blade
<x-oc::toast-stack />   {{-- eenmaal in je layout --}}
```

`ocToast(type, title, description = null, timeout = null)` dispatcht het event `oc-toast`. Buiten Livewire: `window.dispatchEvent(new CustomEvent('oc-toast', { detail: { type: 'info', title: 'Hallo' } }))`.

## DataTable

```php
use Omniacss\Livewire\DataTable;

class UsersTable extends DataTable
{
    protected function columns(): array
    {
        return [
            'name' => 'Naam',
            'email' => ['label' => 'E-mail', 'sortable' => false],
            'balance' => ['label' => 'Saldo', 'numeric' => true],
        ];
    }

    protected function query(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query();
    }

    protected function title(): ?string { return 'Gebruikers'; }

    protected function actions(mixed $row): array
    {
        return [
            ['label' => 'Bewerk', 'href' => route('users.edit', $row)],
            ['label' => 'Verwijder', 'wire' => "delete({$row->id})", 'destructive' => true],
        ];
    }
}
```

```blade
<livewire:users-table />
```

Publieke properties (ook in de URL): `search` (`q`), `sortBy` (`sort`), `sortDirection` (`dir`), `perPage` (`per`). Hooks: `columns()`, `query()`, `title()`, `description()`, `actions($row)`, `cell($key, $row)`, `rows()`. Markup is de `data-table` block.

## Tests

```bash
composer install     # gebruikt ../omniacss-laravel als path repository
vendor/bin/phpunit
```

## Notes

- Dialog-components gebruiken `wire:ignore.self` en Alpine (`$wire.entangle`), mee geleverd met Livewire 3.
- `wire.tag-input` is `wire:ignore`; de state loopt via de entangled array.

MIT
