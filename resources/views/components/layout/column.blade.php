@props([
    'components' => [],
    'adaptiveColSpan' => 12,
    'colSpan' => 12,
])
<div
    {{ $attributes->class(["col-span-$adaptiveColSpan", "xl:col-span-$colSpan", "space-y-5"]) }}
>
    <x-moonshine::components
        :components="$components"
    />

    {{ $slot ?? '' }}
</div>
