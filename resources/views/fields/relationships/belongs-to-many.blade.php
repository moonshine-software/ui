@props([
    'value' => null,
    'component' => null,
    'componentName' => '',
    'buttons' => [],
    'isNullable' => false,
    'isSearchable' => false,
    'isAsyncSearch' => false,
    'isSelectMode' => false,
    'isTreeMode' => false,
    'treeHtml' => '',
    'asyncSearchUrl' => '',
    'isCreatable' => false,
    'createButton' => '',
    'fragmentUrl' => '',
    'relationName' => '',
    'translates' => [],
])
<div x-id="['belongs-to-many']"
     :id="$id('belongs-to-many')"
     data-show-when-field="{{ $attributes->get('name') }}"
>
    @if($isCreatable)
        {!! $createButton !!}

        <x-moonshine::layout.divider />

        @fragment($relationName)
            <div x-data="fragment('{{ $fragmentUrl }}')"
                 @defineEvent('fragment_updated', $relationName, 'fragmentUpdate')
            >
        @endif
            @if($isSelectMode)
                <x-moonshine::form.select
                    :attributes="$attributes->merge([
                        'multiple' => true
                    ])"
                    :nullable="$isNullable"
                    :searchable="$isSearchable"
                    :values="$value"
                    :asyncRoute="$isAsyncSearch ? $asyncSearchUrl : null"
                >
                </x-moonshine::form.select>
            @elseif($isTreeMode)
                <div x-data="tree(@json($value))">
                    {!! $treeHtml !!}
                </div>
            @else
                @if($isAsyncSearch)
                    <div x-data="asyncSearch('{{ $asyncSearchUrl }}')">
                        <div class="dropdown">
                            <x-moonshine::form.input
                                x-model="query"
                                @input.debounce="search"
                                :placeholder="$translates['search']"
                            />
                            <div class="dropdown-body pointer-events-auto visible opacity-100">
                                <div class="dropdown-content">
                                    <ul class="dropdown-menu">
                                        <template x-for="(item, key) in match">
                                            <li class="dropdown-item">
                                                <a href="#"
                                                   class="dropdown-menu-link flex gap-x-2 items-center"
                                                   @click.prevent="select(item)"
                                                >
                                                    <div x-show="item?.properties?.image"
                                                         class="zoom-in h-10 w-10 overflow-hidden rounded-md"
                                                    >
                                                        <img class="h-full w-full object-cover"
                                                              :src="item.properties.image"
                                                              alt=""
                                                        >
                                                    </div>
                                                    <span x-text="item.label" />
                                                </a>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <x-moonshine::layout.divider />

                        <div x-data="pivot"
                             x-init="autoCheck"
                             class="js-pivot-table"
                             data-table-name="{{ $componentName }}"
                        >
                            <x-moonshine::action-group
                                class="mb-4"
                                :actions="$buttons"
                            />

                            {!! $component !!}
                        </div>
                    </div>
                @else
                    <div x-data="pivot" x-init="autoCheck">
                        <x-moonshine::action-group
                            class="mb-4"
                            :actions="$buttons"
                        />

                        {!! $component !!}
                    </div>
                @endif
            @endif
        @if($isCreatable)
            </div>
            @endfragment
        @endif
</div>
