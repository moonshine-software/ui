@props([
    'inDropdown' => false,
    'hasComponent' => false,
    'url' => '#',
    'icon' => '',
    'label' => '',
    'component' => null,
    'badge' => false,
])
@if($attributes->get('type') === 'submit')
    <x-moonshine::form.button
        :attributes="$attributes"
    >
        {!! $slot !!}

        <x-slot:icon>{!! $icon !!}</x-slot:icon>

        {!! $label !!}

        @if($badge !== false)
            <x-moonshine::badge color="">{{ $badge }}</x-moonshine::badge>
        @endif
    </x-moonshine::form.button>
@else
    <x-moonshine::link-button
        :attributes="$attributes"
        :href="$url"
        :badge="$badge"
    >
        {!! $slot !!}

        <x-slot:icon>{!! $icon !!}</x-slot:icon>

        {!! $label !!}
    </x-moonshine::link-button>
@endif

@if($hasComponent)
    <template x-teleport="body">
        {!! $component !!}
    </template>
@endif

