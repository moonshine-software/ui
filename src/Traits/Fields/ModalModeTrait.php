<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\Fields;

use Closure;

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
}