<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\Browser\Pages\Install\AdminPage;
use Tests\Browser\Pages\Install\DatabasePage;
use Tests\Browser\Pages\Install\RequirementsPage;
use Tests\Browser\Pages\Install\WelcomePage;
use Tests\DuskTestCase;

class InstallerTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure application is not installed for installer tests
        $installedFile = storage_path('installed');
        if (file_exists($installedFile)) {
            unlink($installedFile);
        }
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $installedFile = storage_path('installed');
        if (file_exists($installedFile)) {
            unlink($installedFile);
        }

        parent::tearDown();
    }

    #[Test]
    public function welcome_page_displays_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new WelcomePage)
                ->assertVisible('@heading')
                ->assertVisible('@continue-button');
        });
    }

    #[Test]
    public function can_navigate_from_welcome_to_requirements(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new WelcomePage)
                ->click('@continue-button')
                ->on(new RequirementsPage);
        });
    }

    #[Test]
    public function requirements_page_displays_checks(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new RequirementsPage)
                ->assertVisible('@heading')
                ->assertSee('PHP');
        });
    }

    #[Test]
    public function database_page_has_all_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new DatabasePage)
                ->assertVisible('@host-input')
                ->assertVisible('@port-input')
                ->assertVisible('@database-input')
                ->assertVisible('@username-input')
                ->assertVisible('@password-input');
        });
    }

    #[Test]
    public function database_page_has_default_port_value(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new DatabasePage)
                ->assertInputValue('@port-input', '3306');
        });
    }

    #[Test]
    public function admin_page_has_all_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new AdminPage)
                ->assertVisible('@name-input')
                ->assertVisible('@email-input')
                ->assertVisible('@password-input')
                ->assertVisible('@password-confirm-input');
        });
    }

    #[Test]
    public function redirects_to_installer_when_not_installed(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertPathBeginsWith('/install');
        });
    }
}
