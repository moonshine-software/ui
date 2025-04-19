@props([
    'components' => [],
    'bulkButtons' => [],
    'asyncUrl' => '',
    'async' => false,
    'notfound' => false,
    'colSpan' => 12,
    'adaptiveColSpan' => 12,
    'name' => 'default',
    'translates' => [],
    'searchable' => false,
    'searchValue' => '',
    'topLeft' => null,
    'topRight' => null,
])
<div class="js-cards-builder-container">
    <div x-data="cardsBuilder(
    {{ (int) $async }},
    '{{ $asyncUrl }}'
)"
        @defineEventWhen($async, 'cards_updated', $name, 'asyncRequest')
        {{ $attributes }}
    >
        @if($async && $searchable)
            <x-moonshine::layout.flex justify-align="start">
                <x-moonshine::form
                    raw
                    action="{{ $asyncUrl }}"
                    @submit.prevent="asyncFormRequest"
                >
                    <x-moonshine::form.input
                        name="search"
                        type="search"
                        value="{{ $searchValue }}"
                        placeholder="{{ $translates['search'] }}"
                    />
                </x-moonshine::form>

                {!! $topLeft ?? '' !!}
            </x-moonshine::layout.flex>
        @endif


        <x-moonshine::layout.flex justify-align="end">
            {!! $topRight ?? '' !!}
        </x-moonshine::layout.flex>

        <x-moonshine::loader x-show="loading" />
        <div x-show="!loading">
            @if($components->isNotEmpty())
                <x-moonshine::layout.grid>
                    @foreach($components as $card)
                        <x-moonshine::layout.column :colSpan="$colSpan" :adaptiveColSpan="$adaptiveColSpan">
                            {!! $card !!}
                        </x-moonshine::layout.column>
                    @endforeach
                </x-moonshine::layout.grid>

                @if($hasPaginator)
                    {!! $paginator !!}
                @endif
            @else
                <x-moonshine::alert type="default" class="my-4" icon="s.no-symbol">
                    {{ $translates['notfound'] }}
                </x-moonshine::alert>
            @endif
        </div>
    </div>
</div>
