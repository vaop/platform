<?php

declare(strict_types=1);

namespace Services\GitHub;

use Illuminate\Support\Facades\Http;
use Services\Update\Exceptions\UpdateException;

class GitHubReleaseService
{
    private const string API_BASE = 'https://api.github.com';

    private string $owner;

    private string $repo;

    public function __construct()
    {
        $repository = config('vaop.update.repository', 'vaop/platform');
        [$this->owner, $this->repo] = explode('/', $repository, 2);
    }

    /**
     * Get the latest release information.
     *
     * @return array{tag_name: string, name: string, body: string, published_at: string, assets: array}
     *
     * @throws UpdateException
     */
    public function getLatestRelease(): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->get("{$this->apiUrl()}/releases/latest");

        if ($response->status() === 404) {
            throw UpdateException::noReleasesFound();
        }

        if ($response->failed()) {
            throw UpdateException::networkError("GitHub API error: {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Get all releases.
     *
     * @return array<int, array{tag_name: string, name: string, body: string, published_at: string, assets: array, prerelease: bool}>
     *
     * @throws UpdateException
     */
    public function getReleases(int $perPage = 10): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->get("{$this->apiUrl()}/releases", [
            'per_page' => $perPage,
        ]);

        if ($response->failed()) {
            throw UpdateException::networkError("GitHub API error: {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Get a specific release by tag.
     *
     * @return array{tag_name: string, name: string, body: string, published_at: string, assets: array}
     *
     * @throws UpdateException
     */
    public function getReleaseByTag(string $tag): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
        ])->get("{$this->apiUrl()}/releases/tags/{$tag}");

        if ($response->status() === 404) {
            throw UpdateException::noReleasesFound();
        }

        if ($response->failed()) {
            throw UpdateException::networkError("GitHub API error: {$response->status()}");
        }

        return $response->json();
    }

    /**
     * Find the download URL for the release archive.
     *
     * @throws UpdateException
     */
    public function getDownloadUrl(array $release, string $format = 'tar.gz'): string
    {
        // First, check for uploaded release assets
        foreach ($release['assets'] ?? [] as $asset) {
            $name = strtolower($asset['name'] ?? '');
            if (str_ends_with($name, ".{$format}")) {
                return $asset['browser_download_url'];
            }
        }

        // Fall back to GitHub's auto-generated source archives
        $tag = $release['tag_name'];

        return match ($format) {
            'tar.gz' => "https://github.com/{$this->owner}/{$this->repo}/archive/refs/tags/{$tag}.tar.gz",
            'zip' => "https://github.com/{$this->owner}/{$this->repo}/archive/refs/tags/{$tag}.zip",
            default => throw UpdateException::downloadFailed($tag, "Unsupported format: {$format}"),
        };
    }

    /**
     * Parse version from tag name (removes 'v' prefix if present).
     */
    public function parseVersion(string $tag): string
    {
        return ltrim($tag, 'vV');
    }

    /**
     * Compare two versions.
     *
     * @return int -1 if $version1 < $version2, 0 if equal, 1 if $version1 > $version2
     */
    public function compareVersions(string $version1, string $version2): int
    {
        return version_compare(
            $this->parseVersion($version1),
            $this->parseVersion($version2)
        );
    }

    private function apiUrl(): string
    {
        return self::API_BASE."/repos/{$this->owner}/{$this->repo}";
    }
}
