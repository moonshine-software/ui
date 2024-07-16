<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\ActionButton;

use Closure;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Modal;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\HiddenIds;

trait WithModal
{
    protected ?Closure $modal = null;

    public function isInModal(): bool
    {
        return ! is_null($this->modal);
    }

    public function inModal(
        Closure|string|null $title = null,
        Closure|string|null $content = null,
        Closure|string|null $name = null,
        ?Closure $builder = null,
    ): static {
        if(is_null($name)) {
            $name = (string) spl_object_id($this);
        }

        $async = $this->purgeAsyncTap();

        $this->modal = fn (mixed $data) => Modal::make(
            title: fn () => value($title, $data, $this) ?? $this->getLabel(),
            content: fn () => value($content, $data, $this) ?? '',
            asyncUrl: $async ? $this->getUrl($data) : null,
        )
            ->name(value($name, $data, $this))
            ->when(
                ! is_null($builder),
                fn (Modal $modal): Modal => $builder($modal, $this)
            );

        return $this->onBeforeRender(
            static fn (ActionButtonContract $btn): ActionButtonContract => $btn->toggleModal(
                value($name, $btn->getData()?->getOriginal(), $btn)
            )
        );
    }

    public function withConfirm(
        Closure|string|null $title = null,
        Closure|string|null $content = null,
        Closure|string|null $button = null,
        Closure|array|null $fields = null,
        HttpMethod $method = HttpMethod::POST,
        ?Closure $formBuilder = null,
        ?Closure $modalBuilder = null,
        Closure|string|null $name = null,
    ): static {
        $isDefaultMethods = in_array($method, [HttpMethod::GET, HttpMethod::POST], true);
        $async = $this->purgeAsyncTap();

        if ($this->isBulk()) {
            $this->getAttributes()->setAttributes([
                'data-button-type' => 'modal-button',
            ]);
        }

        return $this->inModal(
            fn (mixed $data) => value($title, $data, $this) ?? $this->core->getTranslator()->get('moonshine::ui.confirm'),
            fn (mixed $data): string => (string) FormBuilder::make(
                $this->getUrl($data),
                $isDefaultMethods ? FormMethod::from($method->value) : FormMethod::POST
            )->fields(
                array_filter([
                    $isDefaultMethods
                        ? null
                        : Hidden::make('_method')->setValue($method->value),

                    $this->isBulk()
                        ? HiddenIds::make($this->getBulkForComponent())
                        : null,

                    ...(is_null($fields) ? [] : value($fields, $data)),

                    Heading::make(
                        is_null($content)
                            ? $this->core->getTranslator()->get('moonshine::ui.confirm_message')
                            : value($content, $data)
                    ),
                ])
            )->when(
                $async && ! $this->isAsyncMethod(),
                static fn (FormBuilderContract $form): FormBuilderContract => $form->async()
            )->when(
                $this->isAsyncMethod(),
                fn (FormBuilderContract $form): FormBuilderContract => $form->asyncMethod($this->getAsyncMethod())
            )->submit(
                is_null($button)
                    ? $this->core->getTranslator()->get('moonshine::ui.confirm')
                    : value($button, $data),
                ['class' => 'btn-secondary']
            )->when(
                ! is_null($formBuilder),
                static fn (FormBuilderContract $form): FormBuilderContract => value($formBuilder, $form, $data)
            ),
            name: $name,
            builder: $modalBuilder
        );
    }

    public function getModal(): ?Modal
    {
        return value($this->modal, $this->getData()?->getOriginal(), $this);
    }

    public function toggleModal(string $name = 'default'): static
    {
        return $this->onClick(
            static fn (mixed $data): string => "\$dispatch('" . AlpineJs::event(JsEvent::MODAL_TOGGLED, $name) . "')",
            'prevent'
        );
    }

    public function openModal(): static
    {
        return $this->onClick(static fn (): string => 'toggleModal', 'prevent');
    }
}
