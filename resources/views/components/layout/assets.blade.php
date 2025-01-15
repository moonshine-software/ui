@props([
    'colors' => '',
    'assets' => '',
    'translates' => [],
])

@stack('styles')

{!! $colors !!}
@fragment('moonshine-assets')
{!! $assets !!}
@endfragment

{{ $slot ?? '' }}

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
    const translates = @js($translates);
</script>
