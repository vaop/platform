<?php

declare(strict_types=1);

namespace Tests\Unit\Install\Services;

use App\Install\Services\EnvironmentWriter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EnvironmentWriterTest extends TestCase
{
    private EnvironmentWriter $writer;

    private string $testEnvPath;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a temporary .env file for testing
        $this->testEnvPath = base_path('.env.test');
        file_put_contents($this->testEnvPath, "APP_NAME=TestApp\nAPP_ENV=testing\n");

        // Create writer with custom path using reflection
        $this->writer = new EnvironmentWriter();
        $reflection = new \ReflectionClass($this->writer);
        $property = $reflection->getProperty('envPath');
        $property->setValue($this->writer, $this->testEnvPath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testEnvPath)) {
            unlink($this->testEnvPath);
        }

        parent::tearDown();
    }

    #[Test]
    public function it_can_read_existing_values(): void
    {
        $value = $this->writer->get('APP_NAME');

        $this->assertEquals('TestApp', $value);
    }

    #[Test]
    public function it_returns_default_for_missing_keys(): void
    {
        $value = $this->writer->get('NONEXISTENT_KEY', 'default');

        $this->assertEquals('default', $value);
    }

    #[Test]
    public function it_can_set_a_single_value(): void
    {
        $this->writer->set('NEW_KEY', 'new_value');

        $content = file_get_contents($this->testEnvPath);
        $this->assertStringContainsString('NEW_KEY=new_value', $content);
    }

    #[Test]
    public function it_can_update_existing_values(): void
    {
        $this->writer->set('APP_NAME', 'UpdatedApp');

        $value = $this->writer->get('APP_NAME');
        $this->assertEquals('UpdatedApp', $value);
    }

    #[Test]
    public function it_can_set_multiple_values(): void
    {
        $this->writer->setMultiple([
            'KEY_ONE' => 'value1',
            'KEY_TWO' => 'value2',
        ]);

        $content = file_get_contents($this->testEnvPath);
        $this->assertStringContainsString('KEY_ONE=value1', $content);
        $this->assertStringContainsString('KEY_TWO=value2', $content);
    }

    #[Test]
    public function it_escapes_values_with_spaces(): void
    {
        $this->writer->set('APP_NAME', 'My App Name');

        $content = file_get_contents($this->testEnvPath);
        $this->assertStringContainsString('APP_NAME="My App Name"', $content);
    }

    #[Test]
    public function it_escapes_values_with_special_characters(): void
    {
        $this->writer->set('SPECIAL', 'value with "quotes"');

        $content = file_get_contents($this->testEnvPath);
        $this->assertStringContainsString('SPECIAL="value with \\"quotes\\""', $content);
    }

    #[Test]
    public function it_handles_empty_values(): void
    {
        $this->writer->set('EMPTY_KEY', '');

        $content = file_get_contents($this->testEnvPath);
        $this->assertStringContainsString('EMPTY_KEY=""', $content);
    }

    #[Test]
    public function it_can_generate_app_key(): void
    {
        $key = $this->writer->generateAppKey();

        $this->assertStringStartsWith('base64:', $key);
        $this->assertEquals(44 + 7, strlen($key)); // base64 encoded 32 bytes + 'base64:' prefix

        $storedKey = $this->writer->get('APP_KEY');
        $this->assertEquals($key, $storedKey);
    }

    #[Test]
    public function it_reads_quoted_values_correctly(): void
    {
        file_put_contents($this->testEnvPath, 'QUOTED_VALUE="hello world"' . "\n");

        $value = $this->writer->get('QUOTED_VALUE');

        $this->assertEquals('hello world', $value);
    }

    #[Test]
    public function it_reads_single_quoted_values_correctly(): void
    {
        file_put_contents($this->testEnvPath, "SINGLE_QUOTED='hello world'\n");

        $value = $this->writer->get('SINGLE_QUOTED');

        $this->assertEquals('hello world', $value);
    }
}
