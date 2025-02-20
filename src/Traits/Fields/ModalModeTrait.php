<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\Fields;

use Closure;
use MoonShine\Core\Collections\Components;
use MoonShine\Laravel\Components\Fragment;
use MoonShine\UI\Components\ActionButton;

trait ModalModeTrait
{
    protected bool $isModalMode = false;

    public function modalMode(Closure|bool|null $condition = null): static
    {
        $this->isModalMode = \is_null($condition) || value($condition, $this);

        return $this;
    }

    public function isModalMode(): bool
    {
        return $this->isModalMode;
    }

    public function getModalButton(
        Components $components,
        string $buttonName,
        string $fragmentName
    ): ActionButton {
        return ActionButton::make($buttonName)->inModal(
            title: $this->getLabel(),
            content: (string) Fragment::make($components)->name($fragmentName)
        );
    }
}