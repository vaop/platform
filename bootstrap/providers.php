<?php

return [
    // Twig (order matters: TwigBridge must be first)
    \TwigBridge\ServiceProvider::class,
    \System\View\Providers\TwigServiceProvider::class,

    // Settings
    \System\Settings\Providers\SettingsServiceProvider::class,

    // Events
    \System\Events\Providers\EventServiceProvider::class,
];
