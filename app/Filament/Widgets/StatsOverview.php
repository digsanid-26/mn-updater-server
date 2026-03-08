<?php

namespace App\Filament\Widgets;

use App\Models\Domain;
use App\Models\LicenseKey;
use App\Models\Plugin;
use App\Models\UpdateLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Active Plugins', Plugin::where('is_active', true)->count())
                ->description('Managed plugins')
                ->icon('heroicon-o-puzzle-piece')
                ->color('success'),
            Stat::make('License Keys', LicenseKey::where('is_active', true)->count())
                ->description('Active licenses')
                ->icon('heroicon-o-key')
                ->color('warning'),
            Stat::make('Registered Domains', Domain::where('is_active', true)->count())
                ->description('Active domains')
                ->icon('heroicon-o-globe-alt')
                ->color('info'),
            Stat::make('Update Checks (24h)', UpdateLog::where('action', 'check_update')
                ->where('created_at', '>=', now()->subDay())->count())
                ->description('Last 24 hours')
                ->icon('heroicon-o-arrow-path')
                ->color('primary'),
        ];
    }
}
