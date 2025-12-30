<?php

declare(strict_types=1);

namespace Tests\Unit\System\View\Twig\Security;

use PHPUnit\Framework\Attributes\Test;
use System\View\Twig\Security\ThemeSecurityPolicy;
use Tests\TestCase;
use Twig\Sandbox\SecurityError;

class ThemeSecurityPolicyTest extends TestCase
{
    #[Test]
    public function it_allows_configured_tags(): void
    {
        $policy = new ThemeSecurityPolicy(
            allowedTags: ['if', 'for', 'set'],
            allowedFilters: [],
            allowedFunctions: [],
            allowedMethods: [],
            allowedProperties: []
        );

        // Should not throw
        $policy->checkSecurity(['if', 'for'], [], []);
        $this->assertTrue(true);
    }

    #[Test]
    public function it_blocks_forbidden_tags(): void
    {
        $policy = new ThemeSecurityPolicy(
            allowedTags: ['if', 'for'],
            allowedFilters: [],
            allowedFunctions: [],
            allowedMethods: [],
            allowedProperties: [],
            forbiddenTags: ['sandbox']
        );

        $this->expectException(SecurityError::class);
        $this->expectExceptionMessage('Tag "sandbox" is forbidden');

        $policy->checkSecurity(['sandbox'], [], []);
    }

    #[Test]
    public function it_blocks_disallowed_tags(): void
    {
        $policy = new ThemeSecurityPolicy(
            allowedTags: ['if'],
            allowedFilters: [],
            allowedFunctions: [],
            allowedMethods: [],
            allowedProperties: []
        );

        $this->expectException(SecurityError::class);

        $policy->checkSecurity(['for'], [], []);
    }

    #[Test]
    public function it_allows_configured_filters(): void
    {
        $policy = new ThemeSecurityPolicy(
            allowedTags: [],
            allowedFilters: ['escape', 'upper'],
            allowedFunctions: [],
            allowedMethods: [],
            allowedProperties: []
        );

        // Should not throw
        $policy->checkSecurity([], ['escape', 'upper'], []);
        $this->assertTrue(true);
    }

    #[Test]
    public function it_blocks_disallowed_filters(): void
    {
        $policy = new ThemeSecurityPolicy(
            allowedTags: [],
            allowedFilters: ['escape'],
            allowedFunctions: [],
            allowedMethods: [],
            allowedProperties: []
        );

        $this->expectException(SecurityError::class);

        $policy->checkSecurity([], ['raw'], []);
    }

    #[Test]
    public function it_allows_configured_functions(): void
    {
        $policy = new ThemeSecurityPolicy(
            allowedTags: [],
            allowedFilters: [],
            allowedMethods: [],
            allowedProperties: [],
            allowedFunctions: ['test_fn']
        );

        // Should not throw when function is in the allowed list
        $policy->checkSecurity([], [], ['test_fn']);
        $this->assertTrue(true);
    }

    #[Test]
    public function it_blocks_disallowed_functions(): void
    {
        $policy = new ThemeSecurityPolicy(
            allowedTags: [],
            allowedFilters: [],
            allowedFunctions: ['route'],
            allowedMethods: [],
            allowedProperties: []
        );

        $this->expectException(SecurityError::class);

        $policy->checkSecurity([], [], ['exec']);
    }

    #[Test]
    public function forbidden_tags_take_precedence_over_allowed(): void
    {
        // Even if a tag is in the allowed list, if it's forbidden it should be blocked
        $policy = new ThemeSecurityPolicy(
            allowedTags: ['if', 'sandbox'],
            allowedFilters: [],
            allowedFunctions: [],
            allowedMethods: [],
            allowedProperties: [],
            forbiddenTags: ['sandbox']
        );

        $this->expectException(SecurityError::class);
        $this->expectExceptionMessage('Tag "sandbox" is forbidden');

        $policy->checkSecurity(['sandbox'], [], []);
    }
}
