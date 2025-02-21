@props([
    'href',
    'logo',
    'logoAttributes',
    'logoSmall',
    'logoSmallAttributes',
    'darkLogo' => null,
    'darkSmallLogo' => null,
])
<a {{ $attributes->merge(['class' => 'block', 'rel' => 'home', 'href' => $href]) }}>
    <img src="{{ $logo }}"
        {{ $logoAttributes?->merge([
            'class' => 'hidden h-14 xl:block',
        ]) }}
         alt="{{ $title }}"
        @if($darkLogo) x-show="!$store.darkMode.on" @endif
    />

    @if($darkLogo)
        <img x-show="$store.darkMode.on" src="{{ $darkLogo }}"
             {{ $logoAttributes?->merge([
                 'class' => 'hidden h-14 xl:block',
             ]) }}
             alt="{{ $title }}"
        />
    @endif

    @if($logoSmall)
        <img src="{{ $logoSmall }}"
            {{ $logoSmallAttributes?->merge(['class' => 'block h-8 lg:h-10 xl:hidden']) }}
             alt="{{ $title }}"
             @if($darkSmallLogo) :style="$store.darkMode.on ? 'display: none!important' : ''" @endif
        />
    @endif

    @if($logoSmall && $darkSmallLogo)
        <img src="{{ $darkSmallLogo }}"
             :style="!$store.darkMode.on ? 'display: none!important' : ''"
             {{ $logoSmallAttributes?->merge(['class' => 'block h-8 lg:h-10 xl:hidden']) }}
             alt="{{ $title }}"
        />
    @endif
</a>
