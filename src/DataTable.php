<?php

declare(strict_types=1);

// File: src/DataTable.php | Map: state props -> columns/query hooks -> trash filter (0.17.0) -> column chooser (0.17.0) -> rows -> view

namespace Omniacss\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Livewire data table on the OmniaCSS data-table block: search, sortable columns and pagination.
 *
 *   class UsersTable extends \Omniacss\Livewire\DataTable
 *   {
 *       protected function columns(): array
 *       {
 *           return ['name' => 'Name', 'email' => 'E-mail', 'balance' => ['label' => 'Balance', 'numeric' => true]];
 *       }
 *
 *       protected function query(): \Illuminate\Database\Eloquent\Builder
 *       {
 *           return User::query();
 *       }
 *   }
 *
 * Render it with <livewire:users-table />.
 *
 * 0.17.0: trash pills (active / including trash / trash only, plus "archive only" when you handle it in applyTrashed())
 * show on their own for a model with SoftDeletes, or when trashedOptions() returns them; the choice is $trashed (?trashed=
 * in the URL). While a request runs the table shows skeleton rows (wire:loading). Columns can be hidden with the column
 * chooser ($hidden; a column with 'hideable' => false always shows).
 */
abstract class DataTable extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'sort', except: '')]
    public string $sortBy = '';

    #[Url(as: 'dir', except: 'asc')]
    public string $sortDirection = 'asc';

    #[Url(as: 'per', except: 10)]
    public int $perPage = 10;

    /** Trash filter (0.17.0): active, all, deleted or archived. */
    #[Url(as: 'trashed', except: 'active')]
    public string $trashed = 'active';

    /** Keys of the columns the user hid with the column chooser (0.17.0). */
    public array $hidden = [];

    /**
     * Columns: key => label, or key => ['label' => ..., 'sortable' => bool, 'numeric' => bool, 'searchable' => bool,
     * 'hideBelow' => 'sm'|'md'|'lg' (hide the column when the table is narrower than 24, 32 or 48rem)].
     *
     * @return array<string, string|array<string, mixed>>
     */
    abstract protected function columns(): array;

    /** Base query. Search and sorting are applied on top of it. */
    abstract protected function query(): EloquentBuilder|QueryBuilder;

    protected function title(): ?string
    {
        return null;
    }

    protected function description(): ?string
    {
        return null;
    }

    protected function searchPlaceholder(): string
    {
        return 'Search...';
    }

    /**
     * Table look: 'default', 'striped' (.oc-table--striped), 'compact' (.oc-table--compact), or a list of both.
     *
     * @return string|list<string>
     */
    protected function variant(): string|array
    {
        return 'default';
    }

    /**
     * Texts of the table in another language: any subset of the keys of DataTable::LABELS, e.g.
     * ['actions' => 'Acties', 'showing' => '{from} tot {to} van {total}', 'rowsPerPage' => 'Rijen per pagina'].
     *
     * @return array<string, string>
     */
    protected function labels(): array
    {
        return [];
    }

    /** English texts; {from}, {to}, {total} and {search} are filled in by the view. */
    public const LABELS = [
        'actions' => 'Actions',
        'noResults' => 'No results',
        'nothingMatches' => "Nothing matches \u{201C}{search}\u{201D}.",
        'showing' => 'Showing {from} to {to} of {total} entries',
        'noEntries' => 'No entries',
        'rowsPerPage' => 'Rows per page',
        'pagination' => 'Pagination',
        'previous' => 'Previous',
        'next' => 'Next',
        'trashFilter' => 'Trash filter',
        'activeOnly' => 'Active only',
        'includingTrash' => 'Including trash',
        'trashOnly' => 'Trash only',
        'archiveOnly' => 'Archive only',
        'columns' => 'Columns',
        'loading' => 'Loading',
    ];

    /** @return list<int> */
    protected function perPageOptions(): array
    {
        return [10, 25, 50];
    }

    /**
     * Row actions rendered in the last column: list of ['label' => ..., 'href' => ...] or ['label' => ..., 'wire' => 'delete(1)'].
     * Add 'destructive' => true for the destructive style. With 'icon' => 'mdi:pencil-outline' the action is a square soft
     * icon button (the label becomes its aria-label and tooltip); 'tone' picks its color: primary (default), success,
     * warning, destructive or info.
     *
     * @return list<array<string, mixed>>
     */
    protected function actions(mixed $row): array
    {
        return [];
    }

    /** Cell value. Override for custom formatting (the value is escaped in the view). */
    protected function cell(string $key, mixed $row): mixed
    {
        return data_get($row, $key);
    }

    public function sort(string $column): void
    {
        if (! array_key_exists($column, $this->normalizedColumns()) || ! $this->normalizedColumns()[$column]['sortable']) {
            return;
        }

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * The trash pills to show (0.17.0), in order: any of 'active', 'all', 'deleted', 'archived'. Default: the first three
     * for an Eloquent model with SoftDeletes, none otherwise. Return 'archived' only when applyTrashed() handles it.
     *
     * @return list<string>
     */
    protected function trashedOptions(): array
    {
        $query = $this->query();
        if ($query instanceof EloquentBuilder && in_array(SoftDeletes::class, class_uses_recursive($query->getModel()), true)) {
            return ['active', 'all', 'deleted'];
        }

        return [];
    }

    /**
     * Applies the trash filter $trashed (0.17.0); called whenever trashedOptions() is not empty. Default for SoftDeletes:
     * all = withTrashed(), deleted = onlyTrashed(), active = the model's own scope. Override it for a status or archive
     * column, e.g. `$this->trashed === 'archived' ? $query->where('status', 'Archived') : $query->where('status', '!=', 'Archived')`
     * (keep the parameter type EloquentBuilder|QueryBuilder in the override).
     */
    protected function applyTrashed(EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder
    {
        if (! $query instanceof EloquentBuilder || ! in_array(SoftDeletes::class, class_uses_recursive($query->getModel()), true)) {
            return $query;
        }

        return match ($this->trashed) {
            'all' => $query->withTrashed(),
            'deleted' => $query->onlyTrashed(),
            default => $query,
        };
    }

    /** Choose a trash pill (0.17.0). Unknown values fall back to active. */
    public function setTrashed(string $value): void
    {
        $this->trashed = in_array($value, $this->trashedOptions(), true) ? $value : 'active';
        $this->resetPage();
    }

    public function updatingTrashed(): void
    {
        $this->resetPage();
    }

    /** Show or hide a column (0.17.0); columns with 'hideable' => false stay. */
    public function toggleColumn(string $key): void
    {
        $columns = $this->normalizedColumns();
        if (! isset($columns[$key]) || ! $columns[$key]['hideable']) {
            return;
        }
        $this->hidden = in_array($key, $this->hidden, true)
            ? array_values(array_diff($this->hidden, [$key]))
            : array_values(array_merge($this->hidden, [$key]));
    }

    /** Offer the column chooser in the toolbar (0.17.0). Default false: return true to let users hide columns. */
    protected function columnChooser(): bool
    {
        return false;
    }

    /** Number of skeleton rows while loading (0.17.0). Default: the page size, at most 10. */
    protected function skeletonRows(): int
    {
        return max(1, min(10, $this->perPage));
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    /** Applies search and sorting to the base query. */
    protected function prepare(EloquentBuilder|QueryBuilder $query): EloquentBuilder|QueryBuilder
    {
        $term = trim($this->search);
        $searchable = array_keys(array_filter($this->normalizedColumns(), fn (array $c): bool => $c['searchable']));

        if ($term !== '' && $searchable !== []) {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(function ($q) use ($searchable, $like): void {
                foreach ($searchable as $column) {
                    $q->orWhere($column, 'like', $like);
                }
            });
        }

        $columns = $this->normalizedColumns();
        if ($this->sortBy !== '' && isset($columns[$this->sortBy]) && $columns[$this->sortBy]['sortable']) {
            $query->orderBy($this->sortBy, $this->sortDirection === 'desc' ? 'desc' : 'asc');
        }

        $options = $this->trashedOptions();
        if ($options !== []) {
            if (! in_array($this->trashed, $options, true)) {
                $this->trashed = 'active';
            }
            $query = $this->applyTrashed($query);
        }

        return $query;
    }

    protected function rows(): LengthAwarePaginator
    {
        return $this->prepare($this->query())->paginate(max(1, $this->perPage));
    }

    /**
     * @return array<string, array{label: string, sortable: bool, numeric: bool, searchable: bool, hideable: bool, hideBelow: ?string}>
     */
    protected function normalizedColumns(): array
    {
        $out = [];
        foreach ($this->columns() as $key => $definition) {
            $definition = is_array($definition) ? $definition : ['label' => (string) $definition];
            $out[$key] = [
                'label' => (string) ($definition['label'] ?? $key),
                'sortable' => (bool) ($definition['sortable'] ?? true),
                'numeric' => (bool) ($definition['numeric'] ?? false),
                'searchable' => (bool) ($definition['searchable'] ?? true),
                'hideable' => (bool) ($definition['hideable'] ?? true),
                'hideBelow' => in_array($definition['hideBelow'] ?? $definition['hide-below'] ?? null, ['sm', 'md', 'lg'], true)
                    ? ($definition['hideBelow'] ?? $definition['hide-below'])
                    : null,
            ];
        }

        return $out;
    }

    public function render(): View
    {
        $rows = $this->rows();
        $all = $this->normalizedColumns();
        $visible = array_filter($all, fn (array $c, string $key): bool => ! $c['hideable'] || ! in_array($key, $this->hidden, true), ARRAY_FILTER_USE_BOTH);

        return view('omniacss-livewire::livewire.data-table', [
            'columns' => $visible,
            'allColumns' => $all,
            'chooser' => $this->columnChooser(),
            'trashedOptions' => $this->trashedOptions(),
            'skeletonRows' => $this->skeletonRows(),
            'rows' => $rows,
            'cells' => collect($rows->items())->map(
                fn ($row): array => collect($visible)
                    ->map(fn ($c, $key) => $this->cell($key, $row))
                    ->all()
            )->all(),
            'rowActions' => collect($rows->items())->map(fn ($row): array => $this->actions($row))->all(),
            'title' => $this->title(),
            'description' => $this->description(),
            'placeholder' => $this->searchPlaceholder(),
            'perPageOptions' => $this->perPageOptions(),
            'tableClass' => collect((array) $this->variant())
                ->filter(fn ($v): bool => in_array($v, ['striped', 'compact'], true))
                ->map(fn (string $v): string => 'oc-table--'.$v)
                ->prepend('oc-table')
                ->implode(' '),
            'labels' => array_merge(self::LABELS, $this->labels()),
        ]);
    }
}
