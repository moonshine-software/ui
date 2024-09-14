<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\Fields;

use Closure;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentAttributesBagContract;
use MoonShine\Support\AlpineJs;
use MoonShine\Support\VO\FieldEmptyValue;
use MoonShine\UI\Components\Rating;

trait RangeTrait
{
    public string $fromField = 'from';

    public string $toField = 'to';

    protected ?ComponentAttributesBagContract $fromAttributes = null;

    protected ?ComponentAttributesBagContract $toAttributes = null;

    public function fromAttributes(array $attributes): static
    {
        $this->fromAttributes = $this->getAttributes()
            ->except(array_keys($attributes))
            ->merge($attributes);

        return $this;
    }

    protected function reformatAttributes(
        ?ComponentAttributesBagContract $attributes = null,
        string $name = ''
    ): ComponentAttributesBagContract {
        $dataName = $this->getAttributes()->get('data-name');

        return ($attributes ?? $this->getAttributes())
            ->except(['data-name'])
            ->when(
                $dataName,
                static fn (ComponentAttributesBagContract $attr): ComponentAttributesBagContract => $attr->merge([
                    'data-name' => str($dataName)->replaceLast('[]', "[$name]"),
                ])
            );
    }

    public function getFromAttributes(): ComponentAttributesBagContract
    {
        return $this->reformatAttributes($this->fromAttributes, $this->fromField);
    }

    public function toAttributes(array $attributes): static
    {
        $this->toAttributes = $this->getAttributes()
            ->except(array_keys($attributes))
            ->merge($attributes);

        return $this;
    }

    public function getToAttributes(): ComponentAttributesBagContract
    {
        return $this->reformatAttributes($this->toAttributes, $this->toField);
    }

    public function fromTo(string $fromField, string $toField): static
    {
        $this->fromField = $fromField;
        $this->toField = $toField;

        return $this;
    }

    protected function reformatFilledValue(mixed $data): mixed
    {
        return $this->extractFromTo($data);
    }

    protected function prepareFill(array $raw = [], ?DataWrapperContract $casted = null, int $index = 0): mixed
    {
        $values = parent::prepareFill($raw, $casted);

        // try to get from array
        if ($values instanceof FieldEmptyValue) {
            $castedValue = $raw[$this->getColumn()] ?? false;
            $values = is_array($castedValue)
                ? $castedValue
                : $raw;
        }

        if (empty($values[$this->fromField]) && empty($values[$this->toField])) {
            return new FieldEmptyValue();
        }

        return $values;
    }

    protected function extractFromTo(array $data): array
    {
        return [
            $this->fromField => $data[$this->fromField] ?? data_get($this->getDefault(), $this->fromField, $this->min),
            $this->toField => $data[$this->toField] ?? data_get($this->getDefault(), $this->toField, $this->max),
        ];
    }

    protected function isNullRange(bool $formatted = false): bool
    {
        $value = $formatted
            ? $this->toFormattedValue()
            : $this->toValue(withDefault: false);

        if (is_array($value)) {
            return array_filter($value) === [];
        }

        return true;
    }

    protected function resolveRawValue(): mixed
    {
        return $this->resolvePreview();
    }

    protected function resolvePreview(): string
    {
        $value = $this->toFormattedValue();

        if ($this->isNullRange(formatted: true)) {
            return '';
        }

        $from = $value[$this->fromField] ?? $this->min;
        $to = $value[$this->toField] ?? $this->max;

        if ($this->isRawMode()) {
            return "$from - $to";
        }

        if ($this->isWithStars()) {
            $from = Rating::make(
                (int) $from
            )->render();

            $to = Rating::make(
                (int) $to
            )->render();
        }

        return "$from - $to";
    }

    protected function resolveOnApply(): ?Closure
    {
        return function ($item) {
            $values = $this->getRequestValue();

            if ($values === false) {
                return $item;
            }

            data_set($item, $this->fromField, $values[$this->fromField] ?? '');
            data_set($item, $this->toField, $values[$this->toField] ?? '');

            return $item;
        };
    }

    protected function getOnChangeEventAttributes(?string $url = null): array
    {
        if ($url) {
            $this->fromAttributes(
                AlpineJs::requestWithFieldValue(
                    $url,
                    $this->fromField,
                )
            );

            $this->toAttributes(
                AlpineJs::requestWithFieldValue(
                    $url,
                    $this->toField,
                )
            );
        }

        return [];
    }

    protected function prepareBeforeRender(): void
    {
        parent::prepareBeforeRender();

        $this
            ->fromAttributes([
                'name' => $this->getNameAttribute($this->fromField),
            ])
            ->toAttributes([
                'name' => $this->getNameAttribute($this->toField),
            ]);
    }
}
