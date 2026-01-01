<?php

declare(strict_types=1);

namespace Tests\Unit\System\ExpressionLanguage\Security;

use PHPUnit\Framework\Attributes\Test;
use System\ExpressionLanguage\Exceptions\ExpressionSecurityException;
use System\ExpressionLanguage\Security\ExpressionSecurityPolicy;
use Tests\TestCase;

class ExpressionSecurityPolicyTest extends TestCase
{
    private ExpressionSecurityPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = ExpressionSecurityPolicy::default();
    }

    #[Test]
    public function it_allows_safe_expressions(): void
    {
        $this->policy->validate('distance * 2 + 100');
        $this->policy->validate('speed > 100 and altitude < 35000');
        $this->policy->validate('round(distance, 2)');

        $this->assertTrue(true); // No exception thrown
    }

    #[Test]
    public function it_blocks_dangerous_functions(): void
    {
        $this->expectException(ExpressionSecurityException::class);
        $this->expectExceptionMessage('not allowed');

        $this->policy->validate('eval("malicious code")');
    }

    #[Test]
    public function it_blocks_shell_execution(): void
    {
        $this->expectException(ExpressionSecurityException::class);

        $this->policy->validate('shell_exec("ls")');
    }

    #[Test]
    public function it_blocks_file_operations(): void
    {
        $this->expectException(ExpressionSecurityException::class);

        $this->policy->validate('file_get_contents("/etc/passwd")');
    }

    #[Test]
    public function it_blocks_superglobals(): void
    {
        $this->expectException(ExpressionSecurityException::class);

        $this->policy->validate('$_GET["id"]');
    }

    #[Test]
    public function it_blocks_backtick_operator(): void
    {
        $this->expectException(ExpressionSecurityException::class);
        $this->expectExceptionMessage('not allowed');

        $this->policy->validate('`ls -la`');
    }

    #[Test]
    public function it_allows_default_math_functions(): void
    {
        $this->assertTrue($this->policy->isFunctionAllowed('abs'));
        $this->assertTrue($this->policy->isFunctionAllowed('ceil'));
        $this->assertTrue($this->policy->isFunctionAllowed('floor'));
        $this->assertTrue($this->policy->isFunctionAllowed('round'));
        $this->assertTrue($this->policy->isFunctionAllowed('max'));
        $this->assertTrue($this->policy->isFunctionAllowed('min'));
    }

    #[Test]
    public function it_allows_additional_functions(): void
    {
        $policy = new ExpressionSecurityPolicy(
            allowedFunctions: ['custom_func'],
        );

        $this->assertTrue($policy->isFunctionAllowed('custom_func'));
        $this->assertTrue($policy->isFunctionAllowed('abs')); // Default still works
    }

    #[Test]
    public function it_blocks_explicitly_blocked_functions(): void
    {
        $policy = new ExpressionSecurityPolicy(
            blockedFunctions: ['round'],
        );

        $this->assertFalse($policy->isFunctionAllowed('round'));
        $this->assertTrue($policy->isFunctionAllowed('abs')); // Others still work
    }

    #[Test]
    public function it_creates_restrictive_policy(): void
    {
        $policy = ExpressionSecurityPolicy::restrictive();

        $this->assertTrue($policy->isFunctionAllowed('abs'));
        $this->assertTrue($policy->isFunctionAllowed('round'));
        // Restrictive policy still includes basic math
    }

    #[Test]
    public function it_returns_all_allowed_functions(): void
    {
        $functions = $this->policy->getAllowedFunctions();

        $this->assertContains('abs', $functions);
        $this->assertContains('round', $functions);
        $this->assertContains('max', $functions);
        $this->assertContains('min', $functions);
    }
}
