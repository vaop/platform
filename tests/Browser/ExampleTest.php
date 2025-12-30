<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\Browser\Pages\HomePage;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mark app as installed so it shows the home page
        file_put_contents(storage_path('installed'), '');
    }

    protected function tearDown(): void
    {
        // Clean up
        $installedFile = storage_path('installed');
        if (file_exists($installedFile)) {
            unlink($installedFile);
        }

        parent::tearDown();
    }

    #[Test]
    public function home_page_displays_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(new HomePage)
                ->assertSee('Welcome to')
                ->assertSee('Your virtual airline operations platform');
        });
    }
}
