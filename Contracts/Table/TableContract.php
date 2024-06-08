<?php

declare(strict_types=1);

namespace MoonShine\UI\Contracts\Table;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use MoonShine\UI\Collections\ActionButtons;

interface TableContract
{
    public function getRows(): Collection;

    public function getPaginator(): ?Paginator;

    public function hasPaginator(): bool;

    public function getBulkButtons(): ActionButtons;

    public function trAttributes(Closure $closure): self;

    public function tdAttributes(Closure $closure): self;
}
