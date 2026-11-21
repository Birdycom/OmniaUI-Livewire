{{-- OmniaCSS data-table block, driven by Omniacss\Livewire\DataTable. Markup/classes follow packages/blocks/blocks/data-table.html.
     0.17.0: trash pills, column chooser (popover, stays open while Livewire morphs) and skeleton rows while a request runs
     (wire:loading sets aria-busy on the section; the CSS swaps the rows for the skeleton body). --}}
<section class="oc-card oc-block-datatable" wire:loading.delay.attr="aria-busy">
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
            @if (count($trashedOptions) > 1)
                <div class="oc-filter-pills" role="group" aria-label="{{ $labels['trashFilter'] }}">
                    @foreach ($trashedOptions as $option)
                        <button class="oc-filter-pill" type="button" wire:click="setTrashed('{{ $option }}')" @if ($trashed === $option) aria-current="true"@endif>{{ $labels[['active' => 'activeOnly', 'all' => 'includingTrash', 'deleted' => 'trashOnly', 'archived' => 'archiveOnly'][$option] ?? 'activeOnly'] }}</button>
                    @endforeach
                </div>
            @endif
            <span class="oc-table-toolbar-spacer"></span>
            @if ($chooser)
                <button class="oc-btn oc-btn--outline oc-btn--sm" type="button" popovertarget="{{ $this->getId() }}-columns">{{ $labels['columns'] }}</button>
                <div class="oc-dropdown-menu oc-data-table-colpick" id="{{ $this->getId() }}-columns" popover>
                    <p class="oc-data-table-colpick-title">{{ $labels['columns'] }}</p>
                    @foreach ($allColumns as $key => $column)
                        <label class="oc-dropdown-item" wire:key="oc-col-{{ $key }}"><input type="checkbox" wire:click="toggleColumn('{{ $key }}')" @checked(! $column['hideable'] || ! in_array($key, $hidden, true)) @disabled(! $column['hideable'])> {{ $column['label'] }}</label>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="oc-table-wrap">
            <table class="{{ $tableClass ?? 'oc-table' }}">
                <thead>
                    <tr>
                        @foreach ($columns as $key => $column)
                            <th @if ($column['sortable'] && $sortBy === $key) aria-sort="{{ $sortDirection === 'desc' ? 'descending' : 'ascending' }}" @endif @if ($column['numeric']) data-numeric @endif @if ($column['hideBelow'] ?? null) data-hide-below="{{ $column['hideBelow'] }}" @endif>
                                @if ($column['sortable'])
                                    <a class="oc-table-sort" href="#" wire:click.prevent="sort('{{ $key }}')">{{ $column['label'] }}</a>
                                @else
                                    {{ $column['label'] }}
                                @endif
                            </th>
                        @endforeach
                        <th><span class="oc-visually-hidden">{{ $labels['actions'] }}</span></th>
                    </tr>
                </thead>
                <tbody class="oc-data-table-skeleton-body" aria-hidden="true">
                    @for ($i = 0; $i < $skeletonRows; $i++)
                        <tr class="oc-data-table-skeleton">
                            @foreach ($columns as $key => $column)
                                <td @if ($column['numeric']) data-numeric @endif @if ($column['hideBelow'] ?? null) data-hide-below="{{ $column['hideBelow'] }}" @endif><span class="oc-skeleton oc-data-table-skeleton-bar"></span></td>
                            @endforeach
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
                <tbody class="oc-block-datatable-rows">
                    @forelse ($rows as $index => $row)
                        <tr wire:key="oc-row-{{ $rows->firstItem() + $index }}">
                            @foreach ($columns as $key => $column)
                                <td @if ($column['numeric']) data-numeric @endif @if ($column['hideBelow'] ?? null) data-hide-below="{{ $column['hideBelow'] }}" @endif>{{ $cells[$index][$key] ?? '' }}</td>
                            @endforeach
                            <td class="oc-table-actions">
                                @foreach ($rowActions[$index] ?? [] as $action)
                                    @if (! empty($action['icon']))
                                        @php
                                            $tone = $action['tone'] ?? (empty($action['destructive']) ? 'primary' : 'destructive');
                                        @endphp
                                        @if (! empty($action['href']))
                                            <a class="oc-btn oc-btn--soft-{{ $tone }} oc-btn--icon oc-btn--sm" href="{{ $action['href'] }}" aria-label="{{ $action['label'] }}" title="{{ $action['label'] }}"><iconify-icon icon="{{ $action['icon'] }}" aria-hidden="true"></iconify-icon></a>
                                        @else
                                            <button class="oc-btn oc-btn--soft-{{ $tone }} oc-btn--icon oc-btn--sm" type="button" wire:click="{{ $action['wire'] ?? '' }}" aria-label="{{ $action['label'] }}" title="{{ $action['label'] }}"><iconify-icon icon="{{ $action['icon'] }}" aria-hidden="true"></iconify-icon></button>
                                        @endif
                                    @elseif (! empty($action['href']))
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
                                    <h3 class="oc-empty-title">{{ $labels['noResults'] }}</h3>
                                    @if (trim($search) !== '')
                                        <p class="oc-empty-text">{{ str_replace('{search}', $search, $labels['nothingMatches']) }}</p>
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
                <span>{{ str_replace(['{from}', '{to}', '{total}'], [$rows->firstItem(), $rows->lastItem(), $rows->total()], $labels['showing']) }}</span>
            @else
                <span>{{ $labels['noEntries'] }}</span>
            @endif
            <label>{{ $labels['rowsPerPage'] }}
                <select class="oc-select" wire:model.live="perPage" aria-label="{{ $labels['rowsPerPage'] }}">
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
                <nav aria-label="{{ $labels['pagination'] }}">
                    <ul class="oc-pagination">
                        <li class="oc-pagination-item"><a href="#" wire:click.prevent="previousPage" aria-label="{{ $labels['previous'] }}">&lsaquo;</a></li>
                        @foreach ($pages as $i => $page)
                            @if ($i > 0 && $page - $pages[$i - 1] > 1)
                                <li class="oc-pagination-item" aria-hidden="true">&hellip;</li>
                            @endif
                            <li @class(['oc-pagination-item', 'is-active' => $page === $current])><a href="#" wire:click.prevent="gotoPage({{ $page }})" @if ($page === $current) aria-current="page" @endif>{{ $page }}</a></li>
                        @endforeach
                        <li class="oc-pagination-item"><a href="#" wire:click.prevent="nextPage" aria-label="{{ $labels['next'] }}">&rsaquo;</a></li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
</section>
