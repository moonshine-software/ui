<?php

declare(strict_types=1);

namespace MoonShine\UI\Fields;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\HasFieldsContract;
use MoonShine\UI\Traits\WithFields;

class Template extends Field implements HasFieldsContract
{
    use WithFields;

    protected ?Closure $renderCallback = null;

    public function getPreparedFields(): FieldsContract
    {
        return tap(
            $this->getFields()->wrapNames($this->getColumn()),
            fn () => $this->getFields()
                ->onlyFields()
                ->map(fn (FieldContract $field): FieldContract => $field->setParent($this)->formName($this->getFormName()))
        );
    }

    protected function resolvePreview(): string|Renderable
    {
        return '';
    }

    protected function prepareFill(array $raw = [], mixed $casted = null): mixed
    {
        if($this->isFillChanged()) {
            return value(
                $this->fillCallback,
                $casted ?? $raw,
                $this
            );
        }

        return '';
    }

    public function changeRender(Closure $closure): self
    {
        $this->renderCallback = $closure;

        return $this;
    }

    public function render(): string
    {
        return (string) value($this->renderCallback, $this->toValue(), $this);
    }

    protected function resolveOnApply(): ?Closure
    {
        return static fn ($item) => $item;
    }
}
