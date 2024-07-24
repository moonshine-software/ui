<?php

declare(strict_types=1);

namespace MoonShine\UI\Components;

use Closure;
use MoonShine\Contracts\Core\TypeCasts\CastedDataContract;
use MoonShine\Contracts\UI\FieldContract;
use Throwable;

final class FieldsGroup extends AbstractWithComponents
{
    protected string $view = 'moonshine::components.fields-group';

    /**
     * @throws Throwable
     */
    public function previewMode(): self
    {
        return $this->mapFields(
            static fn (FieldContract $field): FieldContract => $field->previewMode()
        );
    }

    /**
     * @throws Throwable
     */
    public function fill(array $raw = [], ?CastedDataContract $casted = null, int $index = 0): self
    {
        return $this->mapFields(
            static fn (FieldContract $field): FieldContract => $field->fillData(is_null($casted) ? $raw : $casted, $index)
        );
    }

    /**
     * @throws Throwable
     */
    public function withoutWrappers(): self
    {
        return $this->mapFields(
            static fn (FieldContract $field): FieldContract => $field->withoutWrapper()
        );
    }

    /**
     * @param  Closure(FieldContract $field): FieldContract  $callback
     * @throws Throwable
     */
    public function mapFields(Closure $callback): self
    {
        $this->getComponents()
            ->onlyFields()
            ->map(static fn (FieldContract $field): FieldContract => $callback($field));

        return $this;
    }
}
