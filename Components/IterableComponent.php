<?php

declare(strict_types=1);

namespace MoonShine\UI\Components;

// todo(isolate): paginator
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MoonShine\Core\Contracts\CastedData;
use MoonShine\UI\Collections\ActionButtons;
use MoonShine\UI\Contracts\Fields\HasFields;
use MoonShine\UI\Traits\HasDataCast;
use MoonShine\UI\Traits\WithFields;

abstract class IterableComponent extends MoonShineComponent implements HasFields
{
    use HasDataCast;
    use WithFields;

    protected iterable $items = [];

    protected ?Paginator $paginator = null;

    protected array $buttons = [];

    public function items(iterable $items = []): static
    {
        if ($items instanceof Paginator) {
            $this->items = $items->items();
            $this->paginator($items);
        } else {
            $this->items = $items;
        }

        return $this;
    }

    public function getItems(): Collection
    {
        return collect($this->items);
    }

    public function paginator(Paginator $paginator): static
    {
        $this->paginator = $paginator;

        return $this;
    }

    public function getPaginator(): ?Paginator
    {
        return $this->paginator;
    }

    public function hasPaginator(): bool
    {
        return ! is_null($this->paginator);
    }

    public function isSimplePaginator(): bool
    {
        return ! $this->getPaginator() instanceof LengthAwarePaginator;
    }

    public function buttons(array $buttons = []): static
    {
        $this->buttons = $buttons;

        return $this;
    }

    public function hasButtons(): bool
    {
        return $this->buttons !== [];
    }

    public function getButtons(CastedData $data): ActionButtons
    {
        return ActionButtons::make($this->buttons)
            ->fill($data)
            ->onlyVisible()
            ->withoutBulk();
    }

    public function getBulkButtons(): ActionButtons
    {
        return ActionButtons::make($this->buttons)
            ->bulk()
            ->onlyVisible();
    }
}
