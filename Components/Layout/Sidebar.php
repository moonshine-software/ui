<?php

declare(strict_types=1);

namespace MoonShine\UI\Components\Layout;

use MoonShine\Support\Components\MoonShineComponentAttributeBag;
use MoonShine\UI\Components\AbstractWithComponents;

class Sidebar extends AbstractWithComponents
{
    protected string $view = 'moonshine::components.layout.sidebar';

    public bool $collapse = false;

    public MoonShineComponentAttributeBag $collapseAttributes;

    public function __construct(iterable $components = [])
    {
        parent::__construct($components);

        $this->collapseAttributes = new MoonShineComponentAttributeBag();
    }

    public function collapsed(): self
    {
        $this->collapse = true;

        return $this;
    }

    public function collapseAttributes(array $attributes): self
    {
        $this->collapseAttributes = $this->collapseAttributes->merge($attributes);

        return $this;
    }
}
