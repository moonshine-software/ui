<?php

declare(strict_types=1);

namespace MoonShine\UI\Components\Layout;

use MoonShine\Support\Components\MoonShineComponentAttributeBag;
use MoonShine\UI\Components\MoonShineComponent;

/**
 * @method static static make(string $href,string $logo,?string $logoSmall = null,?string $title = null)
 */
final class Logo extends MoonShineComponent
{
    protected string $view = 'moonshine::components.layout.logo';

    public MoonShineComponentAttributeBag $logoAttributes;

    public MoonShineComponentAttributeBag $logoSmallAttributes;

    public function __construct(
        public string $href,
        public string $logo,
        public ?string $logoSmall = null,
        public ?string $title = null,
    ) {
        parent::__construct();

        $this->title ??= moonshineConfig()->getTitle();
        $this->logoAttributes = new MoonShineComponentAttributeBag();
        $this->logoSmallAttributes = new MoonShineComponentAttributeBag();
    }

    public function logoAttributes(array $attributes): self
    {
        $this->logoAttributes = $this->logoAttributes->merge($attributes);

        return $this;
    }

    public function logoSmallAttributes(array $attributes): self
    {
        $this->logoSmallAttributes = $this->logoSmallAttributes->merge($attributes);

        return $this;
    }

    public function minimized(): self
    {
        return $this->logoAttributes([
            ':class' => "minimizedMenu && '!hidden'",
        ])->logoSmallAttributes([
            ':class' => "minimizedMenu && '!block'",
        ]);
    }
}
