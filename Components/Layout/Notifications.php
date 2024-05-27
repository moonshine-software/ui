<?php

declare(strict_types=1);

namespace MoonShine\UI\Components\Layout;

use Illuminate\Support\Collection;
use MoonShine\UI\Components\MoonShineComponent;

final class Notifications extends MoonShineComponent
{
    protected string $view = 'moonshine::components.layout.notifications';

    public Collection $notifications;

    public function __construct()
    {
        parent::__construct();

        $this->notifications = auth()->user()?->unreadNotifications;
    }
}
