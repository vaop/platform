<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Services\Update\UpdateService;

class CheckForUpdatesCommand extends Command
{
    protected $signature = 'vaop:check-updates';

    protected $description = 'Check for available VAOP updates';

    public function handle(UpdateService $updateService): int
    {
        $currentVersion = config('vaop.version');
        $this->info("Current version: v{$currentVersion}");

        $this->output->write('Checking for updates... ');

        try {
            $result = $updateService->checkForUpdate();

            Cache::put('vaop.update_available', [
                'available' => $result['available'],
                'latest' => $result['latest'],
                'checked_at' => now(),
            ], now()->addDay());

            if ($result['available']) {
                $this->output->writeln('<comment>update available!</comment>');
                $this->newLine();
                $this->warn("  New version available: v{$result['latest']}");
                $this->line('  https://github.com/vaop/platform/releases');

                return self::SUCCESS;
            }

            $this->output->writeln('<info>up to date.</info>');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->output->writeln('<error>failed.</error>');
            $this->error("  {$e->getMessage()}");
            Log::warning('Update check failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
