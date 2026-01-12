<?php

namespace App\Filament\Widgets;

use App\Models\Code;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Users Today', User::whereDate('created_at', today())->count())
                ->description('Registered today')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('Users This Month', User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count())
                ->description('Registered this month')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),
            Stat::make('Total Codes', Code::count())
                ->description('All submitted codes')
                ->descriptionIcon('heroicon-m-ticket')
                ->color('primary'),
        ];
    }
}
