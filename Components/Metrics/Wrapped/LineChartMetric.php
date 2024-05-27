<?php

declare(strict_types=1);

namespace MoonShine\UI\Components\Metrics\Wrapped;

use Closure;
use Illuminate\Support\Collection;
use MoonShine\AssetManager\Js;

class LineChartMetric extends Metric
{
    protected string $view = 'moonshine::components.metrics.wrapped.line-chart';

    protected array $lines = [];

    protected array $colors = [];

    protected bool $withoutSortKeys = false;

    public function getAssets(): array
    {
        return [
            Js::make('vendor/moonshine/libs/apexcharts/apexcharts.min.js'),
            Js::make('vendor/moonshine/libs/apexcharts/apexcharts-config.js'),
        ];
    }

    public function line(
        array|Closure $line,
        string|array|Closure $color = '#7843E9'
    ): static {
        $this->lines[] = $line instanceof Closure ? $line() : $line;

        $color = $color instanceof Closure ? $color() : $color;

        if (is_string($color)) {
            $this->colors[] = $color;
        } else {
            $this->colors = $color;
        }

        return $this;
    }

    public function color(int $index): string
    {
        return $this->colors[$index];
    }

    public function colors(): array
    {
        return $this->colors;
    }

    public function labels(): array
    {
        return collect($this->lines())
            ->collapse()
            ->mapWithKeys(fn ($item): mixed => $item)
            ->when(! $this->isWithoutSortKeys(), fn ($items): Collection => $items->sortKeys())
            ->keys()
            ->toArray();
    }

    public function lines(): array
    {
        return $this->lines;
    }

    public function withoutSortKeys(): static
    {
        $this->withoutSortKeys = true;

        return $this;
    }

    public function isWithoutSortKeys(): bool
    {
        return $this->withoutSortKeys;
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'labels' => $this->labels(),
            'lines' => $this->lines(),
            'colors' => $this->colors(),
        ];
    }
}
