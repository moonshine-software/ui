<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\Components;

use MoonShine\UI\Components\MoonShineComponentAttributeBag;
use MoonShine\UI\Fields\Field;

trait WithComponentAttributes
{
    /**
     * The component attributes.
     *
     * @var MoonShineComponentAttributeBag
     */
    public $attributes;

    protected array $withAttributes = [];

    public function attributes(): MoonShineComponentAttributeBag
    {
        return $this->attributes;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes->get($name, $default);
    }

    public function mergeAttribute(string $name, string $value, string $separator = ' '): static
    {
        $this->attributes->concat($name, $value, $separator);

        return $this;
    }

    public function class(string|array $classes): static
    {
        $this->attributes = $this->attributes->class($classes);

        return $this;
    }

    public function style(string|array $styles): static
    {
        $this->attributes = $this->attributes->style($styles);

        return $this;
    }

    public function setAttribute(string $name, string|bool $value): static
    {
        $this->attributes->set($name, $value);

        return $this;
    }

    public function removeAttribute(string $name): static
    {
        $this->attributes->remove($name);

        return $this;
    }

    public function customAttributes(array $attributes, bool $override = false): static
    {
        if($override) {
            foreach (array_keys($attributes) as $name) {
                $this->removeAttribute($name);
            }
        }

        $this->attributes = $this->attributes->merge($attributes);

        return $this;
    }

    public function iterableAttributes(int $level = 0): static
    {
        if (! $this instanceof Field) {
            return $this;
        }

        return $this->customAttributes([
            'data-name' => $this->getNameAttribute(),
            'data-column' => str($this->getColumn())->explode('.')->last(),
            'data-level' => $level,
        ]);
    }
}
