<div>
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-2">
        <div class="flex gap-4">
            @if($headers)
                <div>
                    @foreach($headers as $header)
                        {!! \Illuminate\Support\Facades\Blade::render($header->getLabel()) !!}
                    @endforeach
                </div>
            @endif
        </div>
        <div class="flex gap-2">
            @if($options->bulkActions && count($selected) > 0 && count($bulkActions) > 0)
                <x-dropdown>
                    <x-dropdown.trigger>
                        <x-button variant="outline">
                            {!! __('pengutables::tables.bulk_actions', ['count' => count($selected)]) !!}
                        </x-button>
                    </x-dropdown.trigger>

                    <x-dropdown.items>
                        <div class="p-3 border-b border-gray-200 dark:border-neutral-700">
                            <p class="text-sm text-gray-600 dark:text-neutral-400">
                                {!! __('pengutables::tables.selected_items', ['selected' => count($selected), 'items' => $data->total()]) !!}
                            </p>
                        </div>

                        <div class="p-2 flex flex-col gap-1">
                            @foreach($bulkActions as $action)
                                <x-button
                                    variant="soft"
                                    color="secondary"
                                    class="w-full"
                                    wire:click="executeBulkAction('{{ $action->getLabel() }}')"
                                >
                                    {{ $action->getLabel() }}
                                </x-button>
                            @endforeach
                        </div>
                    </x-dropdown.items>
                </x-dropdown>
            @endif

            @if(count($this->filters()) > 0)
                <x-dropdown>
                    <x-dropdown.trigger>
                        <x-button variant="outline">
                            {!! __('pengutables::tables.filters') !!}
                        </x-button>
                    </x-dropdown.trigger>

                    <x-dropdown.items>
                        <div class="p-3 space-y-3">
                            @foreach($this->filters() as $filter)
                                <div>
                                    @if($filter->type === 'select')
                                        <x-select wire:model.live="activeFilters.{{ $filter->key }}"
                                                  :label="$filter->label">
                                            @foreach($filter->options as $value => $label)
                                                <option value="{{ $value }}">{{ $label }}</option>
                                            @endforeach
                                        </x-select>
                                    @elseif($filter->type === 'date')
                                        <x-input
                                            wire:model.live="activeFilters.{{ $filter->key }}"
                                            type="date"
                                            :label="$filter->label"/>
                                    @elseif($filter->type === 'text')
                                        <x-input
                                            wire:model.live="activeFilters.{{ $filter->key }}"
                                            type="text"
                                            :label="$filter->label"/>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 dark:border-neutral-700 p-3">
                            <x-button
                                class="w-full"
                                wire:click="resetFilters"
                            >
                                {!! __('pengutables::tables.clear_filters') !!}
                            </x-button>
                        </div>
                    </x-dropdown.items>
                </x-dropdown>
            @endif
            @if($options->withExport)
                @php
                    $grouped = collect($options->exportTypes)
                        ->groupBy(fn($type) => str_starts_with($type->value, 'csv') ? 'CSV' : 'XLSX');
                @endphp
                <x-dropdown>
                    <x-dropdown.trigger>
                        <x-button variant="outline">
                            {{ __('pengutables::tables.export') }} {{ count($selected) > 0 ? '(' . count($selected) . ')' : '' }}
                        </x-button>
                    </x-dropdown.trigger>

                    <x-dropdown.items>
                        @foreach($grouped as $fileType => $types)
                            <x-dropdown.item class="cursor-default">
                                {{ $fileType }}
                                @if($typeAll = $types->first(fn($t) => str_ends_with($t->value, '_all')))
                                    <button
                                        wire:click="exportAll('{{ $typeAll->value }}')"
                                        class="font-medium cursor-pointer text-primary underline-offset-2 hover:underline focus:underline focus:outline-hidden dark:text-primary-dark"
                                    >{{ __('pengutables::tables.all') }}
                                    </button>
                                @endif
                                @if($typeSelected = $types->first(fn($t) => str_ends_with($t->value, '_selected')))
                                    <button
                                        wire:click="exportSelected('{{ $typeSelected->value }}')"
                                        class="font-medium cursor-pointer text-primary underline-offset-2 hover:underline focus:underline focus:outline-hidden dark:text-primary-dark">
                                        {{ __('pengutables::tables.selected') }}
                                    </button>
                                @endif
                            </x-dropdown.item>
                        @endforeach
                    </x-dropdown.items>
                </x-dropdown>
            @endif
            @if($options->searchable)
                <div class="flex items-center gap-4">
                    <div class="relative w-full md:w-72">
                        <x-input
                            wire:model.live.debounce.300ms="search"
                            type="text"
                            placeholder="{{ __('pengutables::tables.search_placeholder') }}"
                            leading-icon="search"
                        />
                    </div>

                    @if($options->loading)
                        <x-spinner wire:loading/>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <x-table>
            <x-table.header>
                @if($options->bulkActions)
                    <x-table.header.item class="py-1 px-3 pe-0">
                        <x-checkbox wire:model.live="selectAll"/>
                    </x-table.header.item>
                @endif

                    @foreach($columns as $column)
                        @unless($column->hidden)
                            <x-table.header.item class="py-1 px-2.5 text-sm">
                                @if($column->sortable)
                                    <button
                                        type="button"
                                        class="flex items-center cursor-pointer py-1 gap-1 w-full"
                                        wire:click="sort('{{ $column->key }}')">
                                        <span class="whitespace-nowrap">{{ $column->label }}</span>
                                        <i class="
                                            @if($sortField === $column->key)
                                                icon-chevron-{{ $sortDirection === 'desc' ? 'up' : 'down' }}
                                            @else
                                                icon-chevrons-up-down
                                            @endif
                                            text-xs
                                        "></i>
                                    </button>
                                @else
                                    <div class="w-full whitespace-nowrap">{{ $column->label }}</div>
                                @endif
                            </x-table.header.item>
                        @endunless
                    @endforeach
            </x-table.header>

            <x-table.body>
                @forelse($data as $item)
                    <x-table.body.row>
                        @if($options->bulkActions)
                            <x-table.body.item class="py-3 ps-3">
                                <x-checkbox
                                    wire:model.live="selected"
                                    value="{{ (string)$item->{$options->primaryKey} }}"
                                />
                            </x-table.body.item>
                        @endif

                        @foreach($columns as $column)
                            @unless($column->hidden)
                                <x-table.body.item class="whitespace-nowrap">
                                    {!! $column->getValue($item) !!}
                                </x-table.body.item>
                            @endunless
                        @endforeach
                    </x-table.body.row>
                @empty
                    <x-table.body.row>
                        <x-table.body.item colspan="{{ count($columns) + ($options->bulkActions ? 1 : 0) }}"
                                           class="p-3 pt-8 text-center">
                            {!! __('pengutables::tables.no_results') !!}
                        </x-table.body.item>
                    </x-table.body.row>
                @endforelse
            </x-table.body>
        </x-table>
    </div>

    <div class="flex flex-wrap md:justify-between justify-center items-center gap-2 mt-2">
        @if($options->showItemsPerPage)
            <x-select
                wire:model.live="perPage">
                @foreach($options->perPageOptions as $value)
                    <option value="{{ $value }}">{{ $value }}</option>
                @endforeach
            </x-select>
        @endif

        <div>
            <div class="md:flex items-center gap-2">
                <div class="whitespace-nowrap text-sm text-center">
                    {!! __('pengutables::tables.pagination_text', ['first' => $data->firstItem() ?? 0, 'last' => $data->lastItem() ?? 0, 'total' => $data->total()]) !!}
                </div>

                <x-pengutables::pagination
                    :data="$data"
                    :options="$options"/>
            </div>
        </div>
    </div>
</div>
