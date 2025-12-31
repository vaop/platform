<?php

return [
    // Twig (order matters: TwigBridge must be first)
    \TwigBridge\ServiceProvider::class,
    \System\View\Providers\TwigServiceProvider::class,

    // Settings
    \Domain\Settings\Providers\SettingsServiceProvider::class,
];
