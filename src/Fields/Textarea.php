<?php

declare(strict_types=1);

namespace MoonShine\UI\Fields;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Str;
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
        return Str::wrap(
            $this->isUnescape()
                ? parent::resolvePreview()
                : $this->escapeValue((string) parent::resolvePreview()),
            '<div class="text-clamp">',
            '</div>'
        );
    }
}
