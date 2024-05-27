<?php

declare(strict_types=1);

namespace MoonShine\UI\Components;

use Closure;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Contracts\Actions\ActionButtonContract;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Core\Pages\Page;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\DTOs\AsyncCallback;
use MoonShine\Support\Traits\WithIcon;
use MoonShine\Support\Traits\WithLabel;
use MoonShine\UI\Traits\ActionButton\InDropdownOrLine;
use MoonShine\UI\Traits\ActionButton\WithModal;
use MoonShine\UI\Traits\ActionButton\WithOffCanvas;
use Throwable;

/**
 * @method static static make(Closure|string $label, Closure|string $url = '', mixed $item = null)
 */
class ActionButton extends MoonShineComponent implements ActionButtonContract
{
    use WithLabel;
    use WithIcon;
    use WithOffCanvas;
    use InDropdownOrLine;
    use WithModal;

    protected string $view = 'moonshine::components.action-button';

    protected bool $isBulk = false;

    protected ?string $bulkForComponent = null;

    protected bool $isAsync = false;

    protected ?string $asyncMethod = null;

    protected ?Closure $onBeforeSetCallback = null;

    protected ?Closure $onAfterSetCallback = null;

    public function __construct(
        Closure|string $label,
        protected Closure|string $url = '#',
        protected mixed $item = null
    ) {
        parent::__construct();

        $this->setLabel($label);
    }

    public function setUrl(Closure|string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public static function emptyHidden(): self
    {
        return self::make('')
            ->customAttributes(['style' => 'display:none']);
    }

    public function blank(): self
    {
        $this->customAttributes([
            'target' => '_blank',
        ]);

        return $this;
    }

    //TODO 3.0 Make $forComponent argument required
    public function bulk(?string $forComponent = null): self
    {
        $this->isBulk = true;
        $this->bulkForComponent = $forComponent;

        if(is_null($this->modal)) {
            $this->customAttributes([
                'data-button-type' => 'bulk-button',
                'data-for-component' => $this->bulkForComponent(),
            ]);
        }

        return $this;
    }

    public function isBulk(): bool
    {
        return $this->isBulk;
    }

    public function bulkForComponent(): ?string
    {
        return $this->bulkForComponent;
    }

    public function onBeforeSet(Closure $onBeforeSet): self
    {
        $this->onBeforeSetCallback = $onBeforeSet;

        return $this;
    }

    public function onAfterSet(Closure $onAfterSet): self
    {
        $this->onAfterSetCallback = $onAfterSet;

        return $this;
    }

    public function getItem(): mixed
    {
        return $this->item;
    }

    public function setItem(mixed $item): self
    {
        if(! is_null($this->onBeforeSetCallback)) {
            $item = value($this->onBeforeSetCallback, $item, $this);
        }

        $this->item = $item;

        value($this->onAfterSetCallback, $item, $this);

        return $this;
    }

    public function onClick(Closure $onClick, ?string $modifier = null): self
    {
        $event = 'x-on:click';

        if (! is_null($modifier)) {
            $event .= ".$modifier";
        }

        $this->customAttributes([
            $event => $onClick($this->getItem()),
        ]);

        return $this;
    }

    public function dispatchEvent(array|string $events): self
    {
        return $this->onClick(
            fn (): string => AlpineJs::dispatchEvents($events),
            'prevent'
        );
    }

    /**
     * @throws Throwable
     */
    public function method(
        string $method,
        array|Closure $params = [],
        ?string $message = null,
        ?string $selector = null,
        array $events = [],
        ?AsyncCallback $callback = null,
        ?Page $page = null,
        ?ResourceContract $resource = null
    ): self {
        $this->asyncMethod = $method;

        $this->url = static fn (mixed $item): ?string => moonshineRouter()->asyncMethod(
            method: $method,
            message: $message,
            params: array_filter([
                'resourceItem' => $item instanceof Model ? $item->getKey() : null,
                ...value($params, $item),
            ], static fn ($value) => filled($value)),
            page: $page,
            resource: $resource,
        );

        return $this->async(
            selector: $selector,
            events: $events,
            callback: $callback
        );
    }

    public function isAsync(): bool
    {
        return $this->isAsync;
    }

    public function isAsyncMethod(): bool
    {
        return ! is_null($this->asyncMethod);
    }

    public function asyncMethod(): ?string
    {
        return $this->asyncMethod;
    }

    public function disableAsync(): static
    {
        $this->isAsync = false;

        return $this;
    }

    public function async(
        string $method = 'GET',
        ?string $selector = null,
        array $events = [],
        ?AsyncCallback $callback = null
    ): self {
        $this->isAsync = true;

        return $this->customAttributes([
            'x-data' => 'actionButton',
            ...AlpineJs::asyncUrlDataAttributes(
                method: $method,
                events: $events,
                selector: $selector,
                callback: $callback,
            ),
        ])->onClick(fn (): string => 'request', 'prevent');
    }

    /**
     * @param  array<string, string> $selectors
     */
    public function withSelectorsParams(array $selectors): self
    {
        return $this->customAttributes(
            AlpineJs::asyncSelectorsParamsAttributes($selectors)
        );
    }

    public function hasComponent(): bool
    {
        return $this->isInOffCanvas() || $this->isInModal();
    }

    public function component(): ?MoonShineComponent
    {
        if($this->isInModal()) {
            return $this->modal();
        }

        if($this->isInOffCanvas()) {
            return $this->offCanvas();
        }

        return null;
    }

    public function purgeAsyncTap(): bool
    {
        return tap($this->isAsync(), fn () => $this->purgeAsync());
    }

    /*
     * In this case, the form inside the modal works in async mode,
     * so the async mode is removed from the button.
     */
    public function purgeAsync(): void
    {
        $this->isAsync = false;

        $removeAsyncAttr = array_merge(
            ['x-data'],
            array_keys(AlpineJs::asyncUrlDataAttributes(
                events: ['events'],
                selector: 'selector',
            ))
        );

        if($this->attributes()->get('x-on:click.prevent') === 'request') {
            $removeAsyncAttr[] = 'x-on:click.prevent';
        }

        foreach ($removeAsyncAttr as $name) {
            $this->removeAttribute($name);
        }
    }

    public function getUrl(mixed $data = null): string
    {
        return value($this->url, $data ?? $this->getItem());
    }

    public function primary(Closure|bool|null $condition = null): self
    {
        if (! (value($condition, $this) ?? true)) {
            return $this;
        }

        return $this->class('btn-primary');
    }

    public function secondary(Closure|bool|null $condition = null): self
    {
        if (! (value($condition, $this) ?? true)) {
            return $this;
        }

        return $this->class('btn-secondary');
    }

    public function success(Closure|bool|null $condition = null): self
    {
        if (! (value($condition, $this) ?? true)) {
            return $this;
        }

        return $this->class('btn-success');
    }

    public function warning(Closure|bool|null $condition = null): self
    {
        if (! (value($condition, $this) ?? true)) {
            return $this;
        }

        return $this->class('btn-warning');
    }

    public function error(Closure|bool|null $condition = null): self
    {
        if (! (value($condition, $this) ?? true)) {
            return $this;
        }

        return $this->class('btn-error');
    }

    protected function isSeeParams(): array
    {
        return [
            $this->getItem(),
            $this,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'inDropdown' => $this->inDropdown(),
            'hasComponent' => $this->hasComponent(),
            'component' => $this->hasComponent() ? $this->component() : '',
            'label' => $this->getLabel(),
            'url' => $this->getUrl(),
            'icon' => $this->getIcon(4),
        ];
    }
}
