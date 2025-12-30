<?php

declare(strict_types=1);

namespace App\Console\Commands\Vaop;

use Illuminate\Console\Command;

class VersionCommand extends Command
{
    protected $signature = 'vaop:version';

    protected $description = 'Display the current VAOP version';

    public function handle(): int
    {
        $version = $this->getVersion();

        $this->info("VAOP {$version}");

        return Command::SUCCESS;
    }

    private function getVersion(): string
    {
        return config('vaop.version');
    }
}
