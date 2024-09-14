<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits;

use Closure;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use Throwable;

/**
 * @template T of FieldsContract
 * @mixin ComponentContract
 */
trait WithFields
{
    protected iterable|Closure $fields = [];

    protected ?FieldsContract $preparedFields = null;

    public function resetPreparedFields(): static
    {
        $this->preparedFields = null;

        return $this;
    }

    /**
     * @return T
     * @throws Throwable
     */
    public function getPreparedFields(): FieldsContract
    {
        if (! is_null($this->preparedFields)) {
            return $this->preparedFields;
        }

        return $this->preparedFields = $this->prepareFields();
    }

    /**
     * @return T
     * @throws Throwable
     */
    protected function prepareFields(): FieldsContract
    {
        return $this->getFields();
    }

    /**
     * @return T
     * @throws Throwable
     */
    public function getFields(): FieldsContract
    {
        return $this->getCore()->getFieldsCollection(
            $this->getRawFields()
        );
    }

    public function getRawFields(): iterable
    {
        return value($this->fields, $this);
    }

    /**
     * @throws Throwable
     */
    public function hasFields(): bool
    {
        return $this->getFields()->isNotEmpty();
    }

    /**
     * @param T|Closure(T $ctx): T  $fields
     */
    public function fields(FieldsContract|Closure|iterable $fields): static
    {
        if ($this->getCore()->runningInConsole()) {
            $fields = collect(
                value($fields, $this)
            )
                ->map(static fn (object $field): object => clone $field)
                ->toArray();
        }

        $this->fields = $fields instanceof FieldsContract
            ? $fields->toArray()
            : $fields;

        return $this;
    }

    /**
     * @return T
     * @throws Throwable
     */
    protected function getFilledFields(
        array $raw = [],
        ?DataWrapperContract $casted = null,
        int $index = 0,
        ?FieldsContract $preparedFields = null
    ): FieldsContract {
        $fields = $preparedFields ?? $this->getFields();

        return $fields->fillCloned($raw, $casted, $index, $fields);
    }
}
