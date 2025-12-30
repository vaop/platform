<?php

declare(strict_types=1);

namespace Tests\Unit\Update\Services;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Services\GitHub\GitHubReleaseService;
use Services\Update\Exceptions\UpdateException;
use Tests\TestCase;

class GitHubReleaseServiceTest extends TestCase
{
    private GitHubReleaseService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GitHubReleaseService;
    }

    #[Test]
    public function it_fetches_latest_release(): void
    {
        Http::fake([
            'api.github.com/repos/vaop/platform/releases/latest' => Http::response([
                'tag_name' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'body' => 'Release notes here',
                'published_at' => '2024-01-15T10:00:00Z',
                'assets' => [],
            ], 200),
        ]);

        $release = $this->service->getLatestRelease();

        $this->assertEquals('v1.0.0', $release['tag_name']);
        $this->assertEquals('Version 1.0.0', $release['name']);
        $this->assertEquals('Release notes here', $release['body']);
    }

    #[Test]
    public function it_throws_exception_when_no_releases_found(): void
    {
        Http::fake([
            'api.github.com/repos/vaop/platform/releases/latest' => Http::response([], 404),
        ]);

        $this->expectException(UpdateException::class);
        $this->expectExceptionMessage(__('update.errors.no_releases'));

        $this->service->getLatestRelease();
    }

    #[Test]
    public function it_throws_exception_on_network_error(): void
    {
        Http::fake([
            'api.github.com/repos/vaop/platform/releases/latest' => Http::response([], 500),
        ]);

        $this->expectException(UpdateException::class);
        $this->expectExceptionMessage(__('update.errors.network_error', ['reason' => 'GitHub API error: 500']));

        $this->service->getLatestRelease();
    }

    #[Test]
    public function it_fetches_releases_list(): void
    {
        Http::fake([
            'api.github.com/repos/vaop/platform/releases*' => Http::response([
                ['tag_name' => 'v1.0.0', 'prerelease' => false],
                ['tag_name' => 'v0.9.0', 'prerelease' => false],
                ['tag_name' => 'v0.8.0-beta', 'prerelease' => true],
            ], 200),
        ]);

        $releases = $this->service->getReleases(10);

        $this->assertCount(3, $releases);
        $this->assertEquals('v1.0.0', $releases[0]['tag_name']);
    }

    #[Test]
    public function it_fetches_release_by_tag(): void
    {
        Http::fake([
            'api.github.com/repos/vaop/platform/releases/tags/v1.0.0' => Http::response([
                'tag_name' => 'v1.0.0',
                'name' => 'Version 1.0.0',
                'body' => 'Release notes',
                'assets' => [],
            ], 200),
        ]);

        $release = $this->service->getReleaseByTag('v1.0.0');

        $this->assertEquals('v1.0.0', $release['tag_name']);
    }

    #[Test]
    public function it_gets_download_url_from_assets(): void
    {
        $release = [
            'tag_name' => 'v1.0.0',
            'assets' => [
                ['name' => 'vaop-1.0.0.tar.gz', 'browser_download_url' => 'https://example.com/vaop.tar.gz'],
                ['name' => 'vaop-1.0.0.zip', 'browser_download_url' => 'https://example.com/vaop.zip'],
            ],
        ];

        $url = $this->service->getDownloadUrl($release, 'tar.gz');

        $this->assertEquals('https://example.com/vaop.tar.gz', $url);
    }

    #[Test]
    public function it_falls_back_to_github_archive_url(): void
    {
        $release = [
            'tag_name' => 'v1.0.0',
            'assets' => [],
        ];

        $url = $this->service->getDownloadUrl($release, 'tar.gz');

        $this->assertEquals('https://github.com/vaop/platform/archive/refs/tags/v1.0.0.tar.gz', $url);
    }

    #[Test]
    public function it_parses_version_from_tag(): void
    {
        $this->assertEquals('1.0.0', $this->service->parseVersion('v1.0.0'));
        $this->assertEquals('1.0.0', $this->service->parseVersion('V1.0.0'));
        $this->assertEquals('1.0.0', $this->service->parseVersion('1.0.0'));
        $this->assertEquals('1.0.0-beta', $this->service->parseVersion('v1.0.0-beta'));
    }

    #[Test]
    public function it_compares_versions_correctly(): void
    {
        // Greater than
        $this->assertEquals(1, $this->service->compareVersions('v2.0.0', 'v1.0.0'));
        $this->assertEquals(1, $this->service->compareVersions('1.1.0', '1.0.0'));
        $this->assertEquals(1, $this->service->compareVersions('1.0.1', '1.0.0'));

        // Equal
        $this->assertEquals(0, $this->service->compareVersions('v1.0.0', '1.0.0'));
        $this->assertEquals(0, $this->service->compareVersions('1.0.0', 'v1.0.0'));

        // Less than
        $this->assertEquals(-1, $this->service->compareVersions('v1.0.0', 'v2.0.0'));
        $this->assertEquals(-1, $this->service->compareVersions('1.0.0', '1.1.0'));
    }

    #[Test]
    public function it_compares_prerelease_versions(): void
    {
        // Stable is greater than prerelease
        $this->assertEquals(1, $this->service->compareVersions('1.0.0', '1.0.0-beta'));
        $this->assertEquals(1, $this->service->compareVersions('1.0.0', '1.0.0-alpha'));

        // Dev versions
        $this->assertEquals(1, $this->service->compareVersions('0.0.1', '0.0.0-dev'));
    }
}
