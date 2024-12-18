@props([
    'components' => [],
])
<div {{ $attributes->merge(['class' => 'grid grid-cols-12']) }}>
    <x-moonshine::components
        :components="$components"
    />

    {{ $slot ?? '' }}
</div>
