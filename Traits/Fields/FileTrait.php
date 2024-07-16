<?php

declare(strict_types=1);

namespace MoonShine\UI\Traits\Fields;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use MoonShine\Support\Components\MoonShineComponentAttributeBag;
use MoonShine\UI\Traits\WithStorage;

trait FileTrait
{
    use WithStorage;

    protected array $allowedExtensions = [];

    protected bool $disableDownload = false;

    protected bool $keepOriginalFileName = false;

    protected ?Closure $customName = null;

    protected ?Closure $names = null;

    protected ?Closure $itemAttributes = null;

    /**
     * @param  Closure(string $filename, int $index): string  $closure
     */
    public function names(Closure $closure): static
    {
        $this->names = $closure;

        return $this;
    }

    public function resolveNames(): Closure
    {
        return function (string $filename, int $index = 0): string {
            if(is_null($this->names)) {
                return $filename;
            }

            return (string) value($this->names, $filename, $index);
        };
    }

    /**
     * @param  Closure(string $filename, int $index): string  $closure
     */
    public function itemAttributes(Closure $closure): static
    {
        $this->itemAttributes = $closure;

        return $this;
    }

    public function resolveItemAttributes(): Closure
    {
        return function (string $filename, int $index = 0): MoonShineComponentAttributeBag {
            if(is_null($this->itemAttributes)) {
                return new MoonShineComponentAttributeBag();
            }

            return new MoonShineComponentAttributeBag(
                (array) value($this->itemAttributes, $filename, $index)
            );
        };
    }

    public function keepOriginalFileName(): static
    {
        $this->keepOriginalFileName = true;

        return $this;
    }

    public function isKeepOriginalFileName(): bool
    {
        return $this->keepOriginalFileName;
    }

    public function customName(Closure $name): static
    {
        $this->customName = $name;

        return $this;
    }

    public function getCustomName(): ?Closure
    {
        return $this->customName;
    }

    public function allowedExtensions(array $allowedExtensions): static
    {
        $this->allowedExtensions = $allowedExtensions;

        if ($allowedExtensions !== []) {
            $this->setAttribute('accept', $this->getAcceptExtension());
        }

        return $this;
    }

    public function getAcceptExtension(): string
    {
        $extensions = array_map(
            static fn ($val): string => '.' . $val,
            $this->allowedExtensions
        );

        return implode(',', $extensions);
    }

    public function disableDownload(Closure|bool|null $condition = null): static
    {
        $this->disableDownload = value($condition, $this) ?? true;

        return $this;
    }

    public function canDownload(): bool
    {
        return ! $this->disableDownload;
    }

    public function getPathWithDir(string $value): string
    {
        return $this->getPath($this->getPrependedDir($value));
    }

    public function getPath(string $value): string
    {
        return $this->getStorageUrl($value);
    }

    public function getPrependedDir(string $value): string
    {
        $dir = empty($this->getDir()) ? '' : $this->getDir() . '/';

        return str($value)->remove($dir)
            ->prepend($dir)
            ->value();
    }

    public function getHiddenRemainingValuesKey(): string
    {
        return str('')
            ->when(
                $this->getRequestKeyPrefix(),
                fn (Stringable $str): Stringable => $str->append(
                    $this->getRequestKeyPrefix() . "."
                )
            )
            ->append('hidden_' . $this->getColumn())
            ->value();
    }

    public function getRemainingValues(): Collection
    {
        return collect(
            $this->core->getRequest()->get(
                $this->getHiddenRemainingValuesKey()
            )
        );
    }

    public function isAllowedExtension(string $extension): bool
    {
        return empty($this->getAllowedExtensions())
            || in_array($extension, $this->getAllowedExtensions(), true);
    }

    public function getAllowedExtensions(): array
    {
        return $this->allowedExtensions;
    }

    protected function resolveValue(): mixed
    {
        if ($this->isMultiple() && ! $this->toValue(false) instanceof Collection) {
            return collect($this->toValue(false));
        }

        return parent::resolveValue();
    }

    public function getFullPathValues(): array
    {
        $values = $this->toValue(withDefault: false);

        if (! $values) {
            return [];
        }

        return $this->isMultiple()
            ? collect($values)
                ->map(fn ($value): string => $this->getPathWithDir($value))
                ->toArray()
            : [$this->getPathWithDir($values)];
    }

    public function removeExcludedFiles(): void
    {
        $values = collect(
            $this->toValue(withDefault: false)
        );

        $values->diff($this->getRemainingValues())->each(fn (string $file) => $this->deleteFile($file));
    }
}
