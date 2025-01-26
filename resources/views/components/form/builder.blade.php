@props([
    'name' => '',
    'precognitive' => false,
    'hideSubmit' => false,
    'submitLabel' => '',
    'fields' => [],
    'buttons' => [],
    'errors' => [],
    'errorsAbove' => true,
    'submitAttributes' => null,
])
@if($errorsAbove)
    <x-moonshine::form.all-errors :errors="$errors" />
@endif

<x-moonshine::form
    :attributes="$attributes"
    :name="$name"
    :precognitive="$precognitive"
    :errors="$errors"
>
    <x-moonshine::fields-group
        :components="$fields"
    />

    <x-slot:buttons>
        @if(!($hideSubmit ?? false))
        <x-moonshine::form.button
                :attributes="$submitAttributes?->merge([
                'class' => 'js-form-submit-button',
                'type' => 'submit'
            ])"
        >
            <x-moonshine::spinner
                    color="secondary"
                    class="js-form-submit-button-loader"
                    style="display: none;"
            />

            {{ $submitLabel }}
        </x-moonshine::form.button>
        @else
            <button type="submit" class="js-form-submit-button" style="display: none;">
                <x-moonshine::spinner
                    class="js-form-submit-button-loader"
                />
            </button>
        @endif

        @if($buttons->isNotEmpty())
            <x-moonshine::action-group
                :actions="$buttons"
            />
        @endif
    </x-slot:buttons>
</x-moonshine::form>
