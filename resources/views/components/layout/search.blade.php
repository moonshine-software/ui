@props([
    'enabled' => $isEnabled ?? true,
    'action' => '',
    'value' => '',
    'placeholder' => '',
])
@if($enabled)
    <div {{ $attributes->class(['search']) }}>
        <form action="{{ $action }}"
              x-ref="searchForm"
              class="search-form"
        >
            <x-moonshine::form.input
                x-data="{}"
                x-ref="searchInput"
                name="search"
                @keyup.ctrl.k.window="$refs.searchInput.focus()"
                @keyup.ctrl.period.window="$refs.searchInput.focus()"
                type="search"
                class="search-form-field form-input"
                value="{{ $value }}"
                placeholder="{{ $placeholder }}"
            />

            <button class="search-form-submit" type="submit">
                <x-moonshine::icon
                    icon="magnifying-glass"
                    size="6"
                />
            </button>
        </form>
    </div>
@endif
