<?php

declare(strict_types=1);

namespace App\Admin\Widgets;

use Domain\User\Enums\UserStatus;
use Domain\User\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', UserStatus::Active)->count();
        $pendingUsers = User::where('status', UserStatus::Pending)->count();

        $stats = [
            Stat::make('Total Users', $totalUsers)
                ->icon('heroicon-o-users'),
            Stat::make('Active Users', $activeUsers)
                ->icon('heroicon-o-check-circle')
                ->color('success'),
        ];

        if ($pendingUsers > 0) {
            $stats[] = Stat::make('Pending Approval', $pendingUsers)
                ->icon('heroicon-o-clock')
                ->color('warning');
        }

        return $stats;
    }
}
