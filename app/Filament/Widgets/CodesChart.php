<?php

namespace App\Filament\Widgets;

use App\Models\Code;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CodesChart extends ChartWidget
{
    protected static ?string $heading = 'Code Submissions';

    protected static ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = 'full';

    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last 7 days',
            'month' => 'Last 30 days',
            'quarter' => 'Last 3 months',
            'year' => 'This year',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        // Determine date range and grouping
        switch ($activeFilter) {
            case 'today':
                $startDate = now()->startOfDay();
                $groupBy = 'HOUR(created_at)';
                $dateFormat = 'H:00';
                $periods = 24;
                break;
            case 'week':
                $startDate = now()->subDays(7);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
                $periods = 7;
                break;
            case 'quarter':
                $startDate = now()->subMonths(3);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
                $periods = 90;
                break;
            case 'year':
                $startDate = now()->startOfYear();
                $groupBy = 'DATE_FORMAT(created_at, "%Y-%m")';
                $dateFormat = 'M Y';
                $periods = 12;
                break;
            default: // month
                $startDate = now()->subDays(30);
                $groupBy = 'DATE(created_at)';
                $dateFormat = 'M d';
                $periods = 30;
                break;
        }

        if ($activeFilter === 'today') {
            $data = Code::select(DB::raw('HOUR(created_at) as period'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        } elseif ($activeFilter === 'year') {
            $data = Code::select(DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        } else {
            $data = Code::select(DB::raw('DATE(created_at) as period'), DB::raw('count(*) as count'))
                ->where('created_at', '>=', $startDate)
                ->groupBy('period')
                ->orderBy('period')
                ->get();
        }

        // Fill in missing periods with 0
        $labels = [];
        $counts = [];

        if ($activeFilter === 'today') {
            for ($i = 0; $i < 24; $i++) {
                $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $record = $data->firstWhere('period', $i);
                $counts[] = $record ? $record->count : 0;
            }
        } elseif ($activeFilter === 'year') {
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $period = $date->format('Y-m');
                $labels[] = $date->format('M Y');
                $record = $data->firstWhere('period', $period);
                $counts[] = $record ? $record->count : 0;
            }
        } else {
            for ($i = $periods - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $period = $date->format('Y-m-d');
                $labels[] = $date->format('M d');
                $record = $data->firstWhere('period', $period);
                $counts[] = $record ? $record->count : 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Codes submitted',
                    'data' => $counts,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
