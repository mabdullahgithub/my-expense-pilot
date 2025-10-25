<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Income;
use App\Models\Expense;
use Carbon\Carbon;

class IncomeExpenseChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public ?string $filter = 'last30days';

    protected static bool $isLazy = false;

    public function isCollapsible(): bool
    {
        return true;
    }

    public function getHeading(): string
    {
        return 'Income vs Expense';
    }

    protected function getData(): array
    {
        $data = $this->getChartData();

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $data['incomes'],
                    'backgroundColor' => 'rgba(242, 97, 87, 0.1)', // Primary color with transparency
                    'borderColor' => 'rgb(242, 97, 87)', // Primary color
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
                [
                    'label' => 'Expense',
                    'data' => $data['expenses'],
                    'backgroundColor' => 'rgba(4, 71, 28, 0.1)', // Secondary color with transparency
                    'borderColor' => 'rgb(4, 71, 28)', // Secondary color
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'fill' => true,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getFilters(): ?array
    {
        return [
            'last30days' => 'Last 30 Days',
            'daily' => 'Daily (Last 7 Days)',
            'weekly' => 'Weekly (Last 8 Weeks)',
            'monthly' => 'Monthly (Last 12 Months)',
            'yearly' => 'Yearly (Last 5 Years)',
            'custom' => 'Custom Date Range',
        ];
    }

    public $customStartDate;
    public $customEndDate;

    public function mount(): void
    {
        $this->customStartDate = now()->subDays(30)->format('Y-m-d');
        $this->customEndDate = now()->format('Y-m-d');
    }

    protected function getChartData(): array
    {
        return match($this->filter) {
            'last30days' => $this->getLast30DaysData(),
            'daily' => $this->getDailyData(),
            'weekly' => $this->getWeeklyData(),
            'monthly' => $this->getMonthlyData(),
            'yearly' => $this->getYearlyData(),
            'custom' => $this->getCustomData(),
            default => $this->getLast30DaysData(),
        };
    }

    protected function getLast30DaysData(): array
    {
        $labels = [];
        $incomes = [];
        $expenses = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $incomes[] = Income::where('user_id', auth()->id())
                ->whereDate('entry_date', $date)
                ->sum('amount');

            $expenses[] = Expense::where('user_id', auth()->id())
                ->whereDate('entry_date', $date)
                ->sum('amount');
        }

        return compact('labels', 'incomes', 'expenses');
    }

    protected function getCustomData(): array
    {
        $startDate = $this->customStartDate ? Carbon::parse($this->customStartDate) : Carbon::now()->subDays(30);
        $endDate = $this->customEndDate ? Carbon::parse($this->customEndDate) : Carbon::now();

        $labels = [];
        $incomes = [];
        $expenses = [];

        $daysDiff = $startDate->diffInDays($endDate);

        if ($daysDiff <= 31) {
            // Show daily data for ranges <= 31 days
            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                $labels[] = $currentDate->format('M d');

                $incomes[] = Income::where('user_id', auth()->id())
                    ->whereDate('entry_date', $currentDate)
                    ->sum('amount');

                $expenses[] = Expense::where('user_id', auth()->id())
                    ->whereDate('entry_date', $currentDate)
                    ->sum('amount');

                $currentDate->addDay();
            }
        } else {
            // Show monthly data for ranges > 31 days
            $currentMonth = $startDate->copy()->startOfMonth();
            while ($currentMonth <= $endDate) {
                $labels[] = $currentMonth->format('M Y');

                $incomes[] = Income::where('user_id', auth()->id())
                    ->whereYear('entry_date', $currentMonth->year)
                    ->whereMonth('entry_date', $currentMonth->month)
                    ->whereBetween('entry_date', [$startDate, $endDate])
                    ->sum('amount');

                $expenses[] = Expense::where('user_id', auth()->id())
                    ->whereYear('entry_date', $currentMonth->year)
                    ->whereMonth('entry_date', $currentMonth->month)
                    ->whereBetween('entry_date', [$startDate, $endDate])
                    ->sum('amount');

                $currentMonth->addMonth();
            }
        }

        return compact('labels', 'incomes', 'expenses');
    }

    protected function getDailyData(): array
    {
        $labels = [];
        $incomes = [];
        $expenses = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M d');

            $incomes[] = Income::where('user_id', auth()->id())
                ->whereDate('entry_date', $date)
                ->sum('amount');

            $expenses[] = Expense::where('user_id', auth()->id())
                ->whereDate('entry_date', $date)
                ->sum('amount');
        }

        return compact('labels', 'incomes', 'expenses');
    }

    protected function getWeeklyData(): array
    {
        $labels = [];
        $incomes = [];
        $expenses = [];

        for ($i = 7; $i >= 0; $i--) {
            $startOfWeek = Carbon::now()->subWeeks($i)->startOfWeek();
            $endOfWeek = Carbon::now()->subWeeks($i)->endOfWeek();

            $labels[] = $startOfWeek->format('M d') . ' - ' . $endOfWeek->format('M d');

            $incomes[] = Income::where('user_id', auth()->id())
                ->whereBetween('entry_date', [$startOfWeek, $endOfWeek])
                ->sum('amount');

            $expenses[] = Expense::where('user_id', auth()->id())
                ->whereBetween('entry_date', [$startOfWeek, $endOfWeek])
                ->sum('amount');
        }

        return compact('labels', 'incomes', 'expenses');
    }

    protected function getMonthlyData(): array
    {
        $labels = [];
        $incomes = [];
        $expenses = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $incomes[] = Income::where('user_id', auth()->id())
                ->whereYear('entry_date', $month->year)
                ->whereMonth('entry_date', $month->month)
                ->sum('amount');

            $expenses[] = Expense::where('user_id', auth()->id())
                ->whereYear('entry_date', $month->year)
                ->whereMonth('entry_date', $month->month)
                ->sum('amount');
        }

        return compact('labels', 'incomes', 'expenses');
    }

    protected function getYearlyData(): array
    {
        $labels = [];
        $incomes = [];
        $expenses = [];

        for ($i = 4; $i >= 0; $i--) {
            $year = Carbon::now()->subYears($i)->year;
            $labels[] = (string) $year;

            $incomes[] = Income::where('user_id', auth()->id())
                ->whereYear('entry_date', $year)
                ->sum('amount');

            $expenses[] = Expense::where('user_id', auth()->id())
                ->whereYear('entry_date', $year)
                ->sum('amount');
        }

        return compact('labels', 'incomes', 'expenses');
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => "function(value) { return '$' + value.toLocaleString(); }",
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'maintainAspectRatio' => false,
        ];
    }

}
