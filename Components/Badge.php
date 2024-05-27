<?php

declare(strict_types=1);

namespace MoonShine\UI\Components;

use Illuminate\View\ComponentSlot;

/**
 * @method static static make(string $value = '', string $color = 'purple')
 */
final class Badge extends MoonShineComponent
{
    protected string $view = 'moonshine::components.badge';

    public function __construct(
        public string $value = '',
        public string $color = 'purple'
    ) {
        parent::__construct();
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'slot' => new ComponentSlot($this->value),
        ];
    }
}
