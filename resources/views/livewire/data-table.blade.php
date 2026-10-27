{{-- OmniaCSS data-table block, driven by Omniacss\Livewire\DataTable. Markup/classes follow packages/blocks/blocks/data-table.html. --}}
<section class="oc-card oc-block-datatable">
    @if ($title || $description)
        <header class="oc-block-datatable-head">
            <div>
                @if ($title)
                    <h2 class="oc-block-datatable-title">{{ $title }}</h2>
                @endif
                @if ($description)
                    <p class="oc-block-datatable-description">{{ $description }}</p>
                @endif
            </div>
        </header>
    @endif
    <div class="oc-block-datatable-body">
        <div class="oc-table-toolbar">
            <input class="oc-input" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}">
            <span class="oc-table-toolbar-spacer"></span>
        </div>
        <div class="oc-table-wrap">
            <table class="oc-table">
                <thead>
                    <tr>
                        @foreach ($columns as $key => $column)
                            <th @if ($column['sortable'] && $sortBy === $key) aria-sort="{{ $sortDirection === 'desc' ? 'descending' : 'ascending' }}" @endif @if ($column['numeric']) data-numeric @endif>
                                @if ($column['sortable'])
                                    <a class="oc-table-sort" href="#" wire:click.prevent="sort('{{ $key }}')">{{ $column['label'] }}</a>
                                @else
                                    {{ $column['label'] }}
                                @endif
                            </th>
                        @endforeach
                        <th><span class="oc-visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $index => $row)
                        <tr wire:key="oc-row-{{ $rows->firstItem() + $index }}">
                            @foreach ($columns as $key => $column)
                                <td @if ($column['numeric']) data-numeric @endif>{{ $cells[$index][$key] ?? '' }}</td>
                            @endforeach
                            <td class="oc-table-actions">
                                @foreach ($rowActions[$index] ?? [] as $action)
                                    @if (! empty($action['href']))
                                        <a @class(['oc-table-row-action', 'oc-table-row-action--destructive' => ! empty($action['destructive'])]) href="{{ $action['href'] }}">{{ $action['label'] }}</a>
                                    @else
                                        <button @class(['oc-table-row-action', 'oc-table-row-action--destructive' => ! empty($action['destructive'])]) type="button" wire:click="{{ $action['wire'] ?? '' }}">{{ $action['label'] }}</button>
                                    @endif
                                @endforeach
                            </td>
                        </tr>
                    @empty
                        <tr class="oc-table-empty">
                            <td colspan="{{ count($columns) + 1 }}">
                                <div class="oc-empty">
                                    <h3 class="oc-empty-title">No results</h3>
                                    @if (trim($search) !== '')
                                        <p class="oc-empty-text">Nothing matches &ldquo;{{ $search }}&rdquo;.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="oc-table-footer">
            @if ($rows->total() > 0)
                <span>Showing {{ $rows->firstItem() }} to {{ $rows->lastItem() }} of {{ $rows->total() }} entries</span>
            @else
                <span>No entries</span>
            @endif
            <label>Rows per page
                <select class="oc-select" wire:model.live="perPage" aria-label="Rows per page">
                    @foreach ($perPageOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </label>
            @if ($rows->lastPage() > 1)
                @php
                    $current = $rows->currentPage();
                    $last = $rows->lastPage();
                    $pages = collect(range(1, $last))->filter(fn ($p) => $p === 1 || $p === $last || abs($p - $current) <= 2)->values();
                @endphp
                <nav aria-label="Pagination">
                    <ul class="oc-pagination">
                        <li class="oc-pagination-item"><a href="#" wire:click.prevent="previousPage" aria-label="Previous">&lsaquo;</a></li>
                        @foreach ($pages as $i => $page)
                            @if ($i > 0 && $page - $pages[$i - 1] > 1)
                                <li class="oc-pagination-item" aria-hidden="true">&hellip;</li>
                            @endif
                            <li @class(['oc-pagination-item', 'is-active' => $page === $current])><a href="#" wire:click.prevent="gotoPage({{ $page }})" @if ($page === $current) aria-current="page" @endif>{{ $page }}</a></li>
                        @endforeach
                        <li class="oc-pagination-item"><a href="#" wire:click.prevent="nextPage" aria-label="Next">&rsaquo;</a></li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</section>
