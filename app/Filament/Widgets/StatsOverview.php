<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Income;
use App\Models\Expense;
use Squire\Models\Currency;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected float $total_income = 0;
    protected float $total_expense = 0;
    protected float $total_revenue = 0;

    protected function formatAmount($value)
    {
        $currency = auth()->user()->currency ? auth()->user()->currency : 'usd';
        return Currency::find($currency)->format($value, true);
    }

    protected function getStats(): array
    {
        $this->total_expense = (new Expense())->TotalExpense();
        $this->total_income = (new Income())->TotalIncome();
        $this->total_revenue = $this->total_income - $this->total_expense;

        return [
            Stat::make('Total Income', $this->formatAmount($this->total_income))
                ->description('Total income earned')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Total Expense', $this->formatAmount($this->total_expense))
                ->description('Total expenses incurred')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Total Revenue', $this->formatAmount($this->total_revenue))
                ->description($this->total_revenue > 0 ? 'Profit' : 'Loss')
                ->descriptionIcon($this->total_revenue > 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($this->total_revenue > 0 ? 'success' : 'danger'),
        ];
    }
}
