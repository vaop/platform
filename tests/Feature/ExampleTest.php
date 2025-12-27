<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Mark app as installed so it doesn't redirect to installer
        file_put_contents(storage_path('installed'), '');

        try {
            $response = $this->get('/');
            $response->assertStatus(200);
        } finally {
            unlink(storage_path('installed'));
        }
    }
}
