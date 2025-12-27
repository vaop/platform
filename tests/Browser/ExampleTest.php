<?php

declare(strict_types=1);

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use PHPUnit\Framework\Attributes\Test;
use Tests\DuskTestCase;

class ExampleTest extends DuskTestCase
{
    #[Test]
    public function basic_example(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('VAOP');
        });
    }
}
