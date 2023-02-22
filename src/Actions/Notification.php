<?php

namespace Qruto\Power\Actions;

use Illuminate\Contracts\Container\Container;
use NunoMaduro\LaravelDesktopNotifier\Contracts\Notification as NotificationAlias;
use NunoMaduro\LaravelDesktopNotifier\Contracts\Notifier;

class Notification extends Action
{
    /** No label for silent notification action */
    public static string $label = '';

    /** Make notification as silent to not display it in the console output */
    protected bool $silent = true;

    public function __construct(
        protected Container $container,
        protected string $string,
        protected string $body,
        protected ?string $icon = null,
    ) {
    }

    /** Get notification name */
    public function name(): string
    {
        return $this->string;
    }

    /** Perform desktop notification */
    public function run(): bool
    {
        $notifier = $this->container[Notifier::class];

        $notification = $this->container[NotificationAlias::class]
            ->setTitle($this->string)
            ->setBody($this->body);

        $notification->setIcon(empty($this->icon) ? __DIR__.'/../../laravel-logo.png' : $this->icon);

        return $notifier->send($notification);
    }
}
