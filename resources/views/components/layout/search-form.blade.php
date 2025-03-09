@props([
    'action' => '',
    'value' => '',
    'placeholder' => '',
])
<div {{ $attributes->class(['search']) }}>
    <form action="{{ $action }}"
          x-ref="searchForm"
          class="search-form"
          x-data="{ searchValue: '{{ $value }}' }"
    >
        <x-moonshine::form.input
            x-model="searchValue"
            x-ref="searchInput"
            name="search"
            @keyup.ctrl.k.window="$refs.searchInput.focus()"
            @keyup.ctrl.period.window="$refs.searchInput.focus()"
            type="search"
            class="search-form-field"
            placeholder="{{ $placeholder }}"
            required
        />

        <button
            type="button"
            class="search-form-clear"
            x-show="searchValue"
            @click="searchValue = ''; $refs.searchInput.value = ''; $refs.searchForm.submit()"
        >
            <x-moonshine::icon
                icon="x-mark"
            />
        </button>

        <button class="search-form-submit" type="submit">
            <x-moonshine::icon
                icon="magnifying-glass"
            />
        </button>
    </form>
</div>
