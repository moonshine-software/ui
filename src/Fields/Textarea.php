<?php

declare(strict_types=1);

namespace MoonShine\UI\Fields;

use Illuminate\Contracts\Support\Renderable;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Contracts\DefaultValueTypes\CanBeString;
use MoonShine\UI\Contracts\HasDefaultValueContract;
use MoonShine\UI\Traits\Fields\HasPlaceholder;
use MoonShine\UI\Traits\Fields\WithDefaultValue;
use MoonShine\UI\Traits\Fields\WithEscapedValue;

class Textarea extends Field implements HasDefaultValueContract, CanBeString
{
    use HasPlaceholder;
    use WithDefaultValue;
    use WithEscapedValue;

    protected string $view = 'moonshine::fields.textarea';

    protected function resolvePreview(): Renderable|string
    {
        return Div::make([
            $this->isUnescape()
                ? parent::resolvePreview()
                : $this->escapeValue((string) parent::resolvePreview())
    ])
        ->class('text-clamp')
        ->render();
    }
}
