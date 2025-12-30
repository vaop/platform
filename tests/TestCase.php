<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Get the path to the installed marker file.
     */
    protected function installedPath(): string
    {
        return storage_path('installed');
    }

    /**
     * Mark the application as installed.
     */
    protected function markAsInstalled(): void
    {
        file_put_contents($this->installedPath(), '');
    }

    /**
     * Mark the application as not installed.
     */
    protected function markAsNotInstalled(): void
    {
        @unlink($this->installedPath());
    }

    /**
     * Enable the installer.
     */
    protected function enableInstaller(): void
    {
        config(['vaop.installer.enabled' => true]);
    }

    /**
     * Disable the installer.
     */
    protected function disableInstaller(): void
    {
        config(['vaop.installer.enabled' => false]);
    }

    /**
     * Set up the test environment for installer tests.
     */
    protected function setUpInstaller(): void
    {
        $this->enableInstaller();
        $this->markAsNotInstalled();
    }

    /**
     * Clean up installer test state.
     */
    protected function tearDownInstaller(): void
    {
        $this->markAsNotInstalled();
    }
}
