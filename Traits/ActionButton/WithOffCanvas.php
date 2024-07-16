<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\ActionButton;

use Closure;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\Enums\JsEvent;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\OffCanvas;

trait WithOffCanvas
{
    protected ?Closure $offCanvas = null;

    public function isInOffCanvas(): bool
    {
        return ! is_null($this->offCanvas);
    }

    public function inOffCanvas(
        Closure|string|null $title = null,
        Closure|string|null $content = null,
        Closure|string|null $name = null,
        ?Closure $builder = null,
        iterable $components = [],
    ): static {
        if(is_null($name)) {
            $name = (string) spl_object_id($this);
        }

        $async = $this->purgeAsyncTap();

        $this->offCanvas = fn (mixed $data) => OffCanvas::make(
            title: fn () => value($title, $data, $this) ?? $this->getLabel(),
            content: fn () => value($content, $data, $this) ?? '',
            asyncUrl: $async ? $this->getUrl($data) : null,
            components: $components
        )
            ->name(value($name, $data, $this))
            ->when(
                ! is_null($builder),
                fn (OffCanvas $offCanvas) => $builder($offCanvas, $this)
            );

        return $this->onBeforeRender(
            static fn (ActionButtonContract $btn): ActionButtonContract => $btn->toggleOffCanvas(
                value($name, $btn->getData()?->getOriginal(), $btn)
            )
        );
    }

    public function getOffCanvas(): ?OffCanvas
    {
        return value($this->offCanvas, $this->getData()?->getOriginal(), $this);
    }

    public function toggleOffCanvas(string $name = 'default'): static
    {
        return $this->onClick(
            static fn (): string => "\$dispatch('" . AlpineJs::event(JsEvent::OFF_CANVAS_TOGGLED, $name) . "')",
            'prevent'
        );
    }

    public function openOffCanvas(): static
    {
        return $this->onClick(static fn (): string => 'toggleCanvas', 'prevent');
    }
}
