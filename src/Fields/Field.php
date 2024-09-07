<?php

declare(strict_types=1);

namespace MoonShine\UI\Fields;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Traits\Macroable;
use MoonShine\Contracts\Core\TypeCasts\DataCasterContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Core\TypeCasts\MixedDataWrapper;
use MoonShine\Support\VO\FieldEmptyValue;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Url;
use MoonShine\UI\Contracts\HasDefaultValueContract;
use MoonShine\UI\Traits\Fields\Applies;
use MoonShine\UI\Traits\Fields\ShowWhen;
use MoonShine\UI\Traits\Fields\WithBadge;
use MoonShine\UI\Traits\Fields\WithHint;
use MoonShine\UI\Traits\Fields\WithLink;
use MoonShine\UI\Traits\Fields\WithSorts;
use MoonShine\UI\Traits\WithLabel;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @method static static make(Closure|string|null $label = null, ?string $column = null, ?Closure $formatted = null)
 */
abstract class Field extends FormElement implements FieldContract
{
    use Macroable;
    use WithLabel;
    use WithSorts;
    use WithHint;
    use ShowWhen;
    use WithLink;
    use WithBadge;
    use Applies;

    protected string $column;

    protected ?string $virtualColumn = null;

    protected bool $columnSelection = true;

    protected mixed $value = null;

    protected mixed $resolvedValue = null;

    protected bool $isValueResolved = false;

    protected bool $resolveValueOnce = false;

    protected mixed $rawValue = null;

    protected ?Closure $rawValueCallback = null;

    protected ?Closure $fromRaw = null;

    protected bool $defaultMode = false;

    protected bool $previewMode = false;

    protected bool $rawMode = false;

    protected ?Closure $previewCallback = null;

    protected ?Closure $fillCallback = null;

    protected ?Closure $afterFillCallback = null;

    protected mixed $formattedValue = null;

    protected ?Closure $formattedValueCallback = null;

    protected bool $nullable = false;

    protected bool $isBeforeLabel = false;

    protected bool $isInsideLabel = false;

    protected mixed $data = null;

    protected int $rowIndex = 0;

    protected bool $hasOld = true;

    protected ?Closure $beforeRender = null;

    protected ?Closure $afterRender = null;

    protected ?Closure $renderCallback = null;

    public function __construct(
        Closure|string|null $label = null,
        ?string $column = null,
        ?Closure $formatted = null
    ) {
        parent::__construct();

        $this->setLabel($label ?? $this->getLabel());
        $this->setColumn(
            trim($column ?? str($this->getLabel())->lower()->snake()->value())
        );

        if (! is_null($formatted)) {
            $this->setFormattedValueCallback($formatted);
        }
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function setColumn(string $column): static
    {
        if ($this->showWhenState) {
            foreach (array_keys($this->showWhenCondition) as $key) {
                $this->showWhenCondition[$key]['showField'] = $column;
            }
        }

        $this->column = $column;

        return $this;
    }

    public function virtualColumn(string $column): static
    {
        $this->virtualColumn = $column;

        return $this;
    }

    public function getVirtualColumn(): string
    {
        return $this->virtualColumn ?? $this->getColumn();
    }

    public function columnSelection(bool $active = true): static
    {
        $this->columnSelection = $active;

        return $this;
    }

    public function isColumnSelection(): bool
    {
        return $this->columnSelection;
    }

    protected function prepareFill(array $raw = [], ?DataWrapperContract $casted = null): mixed
    {
        if ($this->isFillChanged()) {
            return value(
                $this->fillCallback,
                is_null($casted) ? $raw : $casted->getOriginal(),
                $this
            );
        }

        $default = new FieldEmptyValue();

        $value = data_get(is_null($casted) ? $raw : $casted->getOriginal(), $this->getColumn(), $default);

        if (is_null($value) || $value === false || $value instanceof FieldEmptyValue) {
            $value = data_get($raw, $this->getColumn(), $default);
        }

        return $value;
    }

    protected function reformatFilledValue(mixed $data): mixed
    {
        return $data;
    }

    protected function resolveFill(array $raw = [], ?DataWrapperContract $casted = null, int $index = 0): static
    {
        $this->setData($casted);
        $this->setRowIndex($index);

        $value = $this->prepareFill($raw, $casted);

        if ($value instanceof FieldEmptyValue) {
            return $this;
        }

        $this->setRawValue($value);

        $value = $this->reformatFilledValue($value);

        $this->setValue($value);

        if (! is_null($this->afterFillCallback)) {
            return value($this->afterFillCallback, $this);
        }

        return $this;
    }

    public function fillData(mixed $value, int $index = 0): static
    {
        $casted = $value instanceof DataWrapperContract
            ? $value
            : new MixedDataWrapper($value);

        return $this->resolveFill(
            $casted->toArray(),
            $casted,
            $index
        );
    }

    public function fillCast(mixed $value, ?DataCasterContract $cast = null, int $index = 0): static
    {
        $casted = $cast ? $cast->cast($value) : new MixedDataWrapper($value);

        return $this->fillData($casted, $index);
    }

    public function fill(mixed $value = null, ?DataWrapperContract $casted = null, int $index = 0): static
    {
        return $this->resolveFill([
            $this->getColumn() => $value,
        ], $casted, $index);
    }

    public function rawMode(Closure|bool|null $condition = null): static
    {
        $this->rawMode = value($condition, $this) ?? true;

        return $this;
    }

    public function isRawMode(): bool
    {
        return $this->rawMode;
    }

    public function toRawValue(): mixed
    {
        if ($this->isRawValueModified()) {
            return value($this->rawValueCallback, $this->rawValue, $this->getData()?->getOriginal(), $this);
        }

        return $this->resolveRawValue();
    }

    protected function resolveRawValue(): mixed
    {
        return $this->rawValue;
    }

    protected function setRawValue(mixed $value = null): static
    {
        $this->rawValue = $value;

        return $this;
    }

    public function setValue(mixed $value = null): static
    {
        $this->value = $value;

        return $this->setRawValue($value);
    }

    protected function setData(?DataWrapperContract $data = null): static
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): ?DataWrapperContract
    {
        return $this->data;
    }

    protected function setRowIndex(int $index = 0): static
    {
        $this->rowIndex = $index;

        return $this;
    }

    public function getRowIndex(): int
    {
        return $this->rowIndex;
    }

    public function toValue(bool $withDefault = true): mixed
    {
        $default = $withDefault && $this instanceof HasDefaultValueContract
            ? $this->getDefault()
            : null;

        return $this->isBlankValue() ? $default : $this->value;
    }

    protected function isBlankValue(): bool
    {
        return is_null($this->value);
    }

    public function getValue(bool $withOld = true): mixed
    {
        if ($this->isValueResolved && $this->resolveValueOnce) {
            return $this->resolvedValue;
        }

        if (! $this->hasOld) {
            $withOld = false;
        }

        $empty = new FieldEmptyValue();
        $old = $withOld
            ? $this->getCore()->getRequest()->getOld($this->getNameDot(), $empty)
            : $empty;

        if ($withOld && $old !== $empty) {
            return $old;
        }

        $this->isValueResolved = true;

        return $this->resolvedValue = $this->resolveValue();
    }

    protected function resolveValue(): mixed
    {
        return $this->toValue();
    }

    protected function setFormattedValue(mixed $value = null): static
    {
        $this->formattedValue = $value;

        return $this;
    }

    protected function setFormattedValueCallback(Closure $formattedValueCallback): void
    {
        $this->formattedValueCallback = $formattedValueCallback;
    }

    public function getFormattedValueCallback(): ?Closure
    {
        return $this->formattedValueCallback;
    }

    public function toFormattedValue(): mixed
    {
        if (! is_null($this->getFormattedValueCallback())) {
            $this->setFormattedValue(
                value(
                    $this->getFormattedValueCallback(),
                    $this->getData()?->getOriginal(),
                    $this->getRowIndex()
                )
            );
        }

        return $this->formattedValue ?? $this->toValue(withDefault: false);
    }

    /**
     * @param  Closure(mixed $data, self $field): mixed  $callback
     */
    public function changeFill(Closure $callback): static
    {
        $this->fillCallback = $callback;

        return $this;
    }

    /**
     * @param  Closure(static $ctx): static  $callback
     */
    public function afterFill(Closure $callback): static
    {
        $this->afterFillCallback = $callback;

        return $this;
    }

    public function isFillChanged(): bool
    {
        return ! is_null($this->fillCallback);
    }

    /**
     * @param  Closure(mixed $value, static $field): mixed  $callback
     */
    public function changePreview(Closure $callback): static
    {
        $this->previewCallback = $callback;

        return $this;
    }

    public function isPreviewChanged(): bool
    {
        return ! is_null($this->previewCallback);
    }

    public function isPreviewMode(): bool
    {
        return $this->previewMode;
    }

    public function previewMode(): static
    {
        $this->previewMode = true;

        return $this;
    }

    public function isDefaultMode(): bool
    {
        return $this->defaultMode;
    }

    public function defaultMode(): static
    {
        $this->defaultMode = true;

        return $this;
    }

    public function isRawValueModified(): bool
    {
        return ! is_null($this->rawValueCallback);
    }

    /**
     * @param  Closure(mixed $raw, mixed $original, static): mixed  $callback
     *
     * @return $this
     */
    public function modifyRawValue(Closure $callback): static
    {
        $this->rawValueCallback = $callback;

        return $this;
    }

    /**
     * @param  Closure(mixed $raw, static): mixed  $callback
     *
     * @return $this
     */
    public function fromRaw(Closure $callback): static
    {
        $this->fromRaw = $callback;

        return $this;
    }

    public function getValueFromRaw(mixed $raw): mixed
    {
        if (is_null($this->fromRaw)) {
            return $raw;
        }

        return value($this->fromRaw, $raw, $this);
    }

    public function preview(): Renderable|string
    {
        if ($this->isRawMode()) {
            return (string) ($this->toRawValue() ?? '');
        }

        if ($this->isPreviewChanged()) {
            return (string) value(
                $this->previewCallback,
                $this->toValue(),
                $this,
            );
        }

        $preview = $this->resolvePreview();

        return $this->previewDecoration($preview);
    }

    protected function resolvePreview(): Renderable|string
    {
        return (string) ($this->toFormattedValue() ?? '');
    }

    private function previewDecoration(Renderable|string $value): Renderable|string
    {
        if ($value instanceof Renderable) {
            return $value->render();
        }

        if ($this->hasLink()) {
            $href = $this->getLinkValue($value);

            $value = (string) Url::make(
                href: $href,
                value: $this->getLinkName($value) ?: $value,
                icon: $this->getLinkIcon(),
                withoutIcon: $this->isWithoutIcon(),
                blank: $this->isLinkBlank()
            )->render();
        }

        if ($this->isBadge()) {
            return Badge::make((string) $value, $this->getBadgeColor($this->toValue()))
                ->render();
        }

        return $value;
    }

    public function reset(): static
    {
        return $this
            ->setValue()
            ->setRawValue()
            ->setFormattedValue();
    }

    public function nullable(Closure|bool|null $condition = null): static
    {
        $this->nullable = value($condition, $this) ?? true;

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function insideLabel(): static
    {
        $this->isInsideLabel = true;

        return $this;
    }

    public function isInsideLabel(): bool
    {
        return $this->isInsideLabel;
    }

    public function beforeLabel(): static
    {
        $this->isBeforeLabel = true;

        return $this;
    }

    public function isBeforeLabel(): bool
    {
        return $this->isBeforeLabel;
    }

    /**
     * @param  Closure(static $ctx): mixed  $callback
     */
    public function beforeRender(Closure $callback): static
    {
        $this->beforeRender = $callback;

        return $this;
    }

    public function getBeforeRender(): Renderable|string
    {
        return is_null($this->beforeRender)
            ? ''
            : value($this->beforeRender, $this);
    }

    /**
     * @param  Closure(static $ctx): mixed  $callback
     */
    public function afterRender(Closure $callback): static
    {
        $this->afterRender = $callback;

        return $this;
    }

    public function getAfterRender(): Renderable|string
    {
        return is_null($this->afterRender)
            ? ''
            : value($this->afterRender, $this);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function prepareBeforeRender(): void
    {
        if (! is_null($this->onChangeUrl) && $this->isOnChangeCondition()) {
            $onChangeUrl = value($this->onChangeUrl, $this->getData(), $this->toValue(), $this);

            $this->customAttributes(
                $this->getOnChangeEventAttributes($onChangeUrl),
            );
        }

        if (! $this->isPreviewMode()) {
            $id = $this->attributes->get('id');

            $this->customAttributes([
                $id ? 'id' : ':id' => $id ?? "\$id(`field-{$this->getFormName()}`)",
                'name' => $this->getNameAttribute(),
            ]);

            $this->resolveValidationErrorClasses();
        }
    }

    /**
     * @param  Closure(mixed $value, static $ctx): static  $callback
     */
    public function changeRender(Closure $callback): static
    {
        $this->renderCallback = $callback;

        return $this;
    }

    public function isRenderChanged(): bool
    {
        return ! is_null($this->renderCallback);
    }

    protected function prepareRender(Renderable|Closure|string $view): Renderable|Closure|string
    {
        if (! $this->isPreviewMode() && $this->hasWrapper()) {
            return (new FieldContainer(
                field: $this,
                slot: $view,
            ))->render();
        }

        return $view;
    }

    protected function resolveRender(): Renderable|Closure|string
    {
        if (! $this->isDefaultMode() && $this->isRawMode()) {
            $this->previewMode();
        }

        if (! $this->isDefaultMode() && $this->isPreviewMode()) {
            return $this->preview();
        }

        if ($this->isRenderChanged()) {
            return value(
                $this->renderCallback,
                $this->toValue(),
                $this,
            );
        }

        if ($this->getView() === '') {
            return $this->toValue();
        }

        return $this->renderView();
    }

    protected function systemViewData(): array
    {
        return [
            ...parent::systemViewData(),
            'label' => $this->getLabel(),
            'column' => $this->getColumn(),
            'value' => $this->getValue(),
            'isNullable' => $this->isNullable(),
        ];
    }
}
