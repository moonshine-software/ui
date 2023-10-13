<?php

namespace MoonShine\UI;

use Closure;
use MoonShine\Traits\Makeable;

/**
 * @method static static make(string|Closure|null $title, string|Closure|null $content, bool $isLeft = false)
 */
class OffCanvas
{
    use Makeable;

    public function __construct(
        protected string|Closure|null $title,
        protected string|Closure|null $content,
        protected bool $isLeft = false
    ) {
    }

    public function isLeft(): bool
    {
        return $this->isLeft;
    }

    public function title(mixed $data = null): ?string
    {
        return value($this->title, $data);
    }

    public function content(mixed $data = null): ?string
    {
        return value($this->content, $data);
    }
}
