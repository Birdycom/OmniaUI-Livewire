<?php

declare(strict_types=1);

// File: src/DataTable.php | Map: state props -> columns/query hooks -> rows -> view

namespace Omniacss\Livewire;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
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

    /**
     * Columns: key => label, or key => ['label' => ..., 'sortable' => bool, 'numeric' => bool, 'searchable' => bool].
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

    /** @return list<int> */
    protected function perPageOptions(): array
    {
        return [10, 25, 50];
    }

    /**
     * Row actions rendered in the last column: list of ['label' => ..., 'href' => ...] or ['label' => ..., 'wire' => 'delete(1)'].
     * Add 'destructive' => true for the destructive style.
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

        return $query;
    }

    protected function rows(): LengthAwarePaginator
    {
        return $this->prepare($this->query())->paginate(max(1, $this->perPage));
    }

    /**
     * @return array<string, array{label: string, sortable: bool, numeric: bool, searchable: bool}>
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
            ];
        }

        return $out;
    }

    public function render(): View
    {
        $rows = $this->rows();

        return view('omniacss-livewire::livewire.data-table', [
            'columns' => $this->normalizedColumns(),
            'rows' => $rows,
            'cells' => collect($rows->items())->map(
                fn ($row): array => collect($this->normalizedColumns())
                    ->map(fn ($c, $key) => $this->cell($key, $row))
                    ->all()
            )->all(),
            'rowActions' => collect($rows->items())->map(fn ($row): array => $this->actions($row))->all(),
            'title' => $this->title(),
            'description' => $this->description(),
            'placeholder' => $this->searchPlaceholder(),
            'perPageOptions' => $this->perPageOptions(),
        ]);
    }
}
