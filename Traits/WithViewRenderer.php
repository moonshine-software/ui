<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits;

use Closure;
use Illuminate\Contracts\View\View;
use MoonShine\UI\Contracts\Components\HasCanSeeContract;
use MoonShine\UI\Contracts\Components\HasComponents;
use MoonShine\UI\Contracts\Fields\HasFields;
use MoonShine\UI\Contracts\MoonShineRenderable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Throwable;

trait WithViewRenderer
{
    protected string $view = '';

    protected ?string $customView = null;

    /**
     * @var array<string, mixed>|Closure
     */
    protected array|Closure $customViewData = [];

    protected ?Closure $onBeforeRenderCallback = null;

    private View|string|null $cachedRender = null;

    public function getView(): string
    {
        return $this->customView ?? $this->view;
    }

    /**
     * @return array<string, mixed>
     */
    public function getCustomViewData(): array
    {
        return value($this->customViewData, $this);
    }

    public function customView(string $view, array $data = []): static
    {
        $this->customView = $view;
        $this->customViewData = $data;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function systemViewData(): array
    {
        return [];
    }

    protected function prepareBeforeRender(): void
    {
        //
    }

    protected function resolveAssets(): void
    {
        moonshineAssets()->add($this->getAssets());
    }

    public function shouldRender(): bool
    {
        return $this instanceof HasCanSeeContract
            ? $this->isSee(...$this->isSeeParams())
            : true;
    }

    public function onBeforeRender(Closure $onBeforeRender): static
    {
        $this->onBeforeRenderCallback = $onBeforeRender;

        return $this;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    public function render(): View|Closure|string
    {
        if(! $this->shouldRender()) {
            return '';
        }

        if (! is_null($this->cachedRender)) {
            return $this->cachedRender;
        }

        $this->prepareBeforeRender();

        if(! is_null($this->onBeforeRenderCallback)) {
            value($this->onBeforeRenderCallback, $this);
        }

        $view = $this->resolveRender();

        return $this->cachedRender = $this->prepareRender($view);
    }

    protected function prepareRender(View|Closure|string $view): View|Closure|string
    {
        return $view;
    }

    protected function resolveRender(): View|Closure|string
    {
        return $this->renderView();
    }

    protected function renderView(): View|Closure|string
    {
        return moonshine()->render(
            $this->getView(),
            $this->toArray(),
            $this,
        );
    }

    public function toStructure(bool $withStates = true): array
    {
        $components = [];
        $states = $withStates ? $this->toArray() : [];

        $states = data_forget($states, 'componentName');
        $states = data_forget($states, 'components');
        $states = data_forget($states, 'fields');

        if($this instanceof HasComponents) {
            $components = $this->getComponents()
                ->map(fn (MoonShineRenderable $component): array => $component->toStructure($withStates));
        }

        if($this instanceof HasFields) {
            $components = $this->getFields()
                ->map(fn (MoonShineRenderable $component): array => $component->toStructure($withStates));

            $states['fields'] = $components;
        }

        return array_filter([
            'type' => class_basename($this),
            'components' => $components,
            'states' => $states,
        ]);
    }

    public function toArray(): array
    {
        return [
            ...$this->viewData(),
            ...$this->getCustomViewData(),
            ...$this->systemViewData(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function __toString(): string
    {
        return (string) $this->render();
    }

    public function escapeWhenCastingToString($escape = true): static
    {
        return $this;
    }
}
