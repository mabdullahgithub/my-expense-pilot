<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\EmploymentHistory;
use App\Models\Income;
use App\Models\Expense;
use Squire\Models\Currency;

class FinancialInsightsWidget extends BaseWidget
{
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    public function isCollapsible(): bool
    {
        return true;
    }

    public function getHeading(): string
    {
        $profileLabels = implode(', ', auth()->user()->getProfileTypeLabels());
        return 'Financial Insights - ' . $profileLabels;
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $profileTypes = is_array($user->profile_type) ? $user->profile_type : [];
        $primaryType = !empty($profileTypes) ? $profileTypes[0] : null;

        switch ($primaryType) {
            case 'student':
                return $this->getStudentStats();
            case 'employee':
                return $this->getEmployeeStats();
            case 'business_owner':
                return $this->getBusinessOwnerStats();
            case 'freelancer':
                return $this->getFreelancerStats();
            default:
                return $this->getEmployeeStats();
        }
    }

    private function getStudentStats(): array
    {
        $partTimeIncome = Income::where('user_id', auth()->id())
            ->whereMonth('entry_date', now()->month)
            ->sum('amount');

        $expenses = Expense::where('user_id', auth()->id())
            ->whereMonth('entry_date', now()->month)
            ->sum('amount');

        $balance = $partTimeIncome - $expenses;

        return [
            Stat::make('Part-time Income', $this->formatAmount($partTimeIncome))
                ->description('This month from part-time work')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),
            
            Stat::make('Monthly Balance', $this->formatAmount($balance))
                ->description($balance >= 0 ? 'Positive balance' : 'Spending more than earning')
                ->descriptionIcon($balance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($balance >= 0 ? 'success' : 'danger'),
                
            Stat::make('Student Tip', 'Save 10%')
                ->description('Try to save at least 10% of income')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info'),
        ];
    }

    private function getEmployeeStats(): array
    {
        $currentJob = auth()->user()->currentEmployment()->first();
        $monthlySalary = $currentJob ? $this->calculateMonthlySalary($currentJob) : 0;
        $monthlyExpenses = Expense::where('user_id', auth()->id())
            ->whereMonth('entry_date', now()->month)
            ->sum('amount');

        $savingsRate = $monthlySalary > 0 ? (($monthlySalary - $monthlyExpenses) / $monthlySalary) * 100 : 0;

        return [
            Stat::make('Monthly Salary', $this->formatAmount($monthlySalary))
                ->description($currentJob ? 'From ' . $currentJob->company_name : 'No current employment')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
                
            Stat::make('Savings Rate', round($savingsRate, 1) . '%')
                ->description($savingsRate > 20 ? 'Excellent!' : ($savingsRate > 10 ? 'Good progress' : 'Consider reducing expenses'))
                ->descriptionIcon('heroicon-m-chart-pie')
                ->color($savingsRate > 20 ? 'success' : ($savingsRate > 10 ? 'warning' : 'danger')),
                
            Stat::make('Career Progress', auth()->user()->employmentHistory()->count() . ' positions')
                ->description('Your career progression')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
        ];
    }

    private function getBusinessOwnerStats(): array
    {
        $monthlyRevenue = Income::where('user_id', auth()->id())
            ->whereMonth('entry_date', now()->month)
            ->sum('amount');

        $monthlyExpenses = Expense::where('user_id', auth()->id())
            ->whereMonth('entry_date', now()->month)
            ->sum('amount');

        $profit = $monthlyRevenue - $monthlyExpenses;
        $profitMargin = $monthlyRevenue > 0 ? ($profit / $monthlyRevenue) * 100 : 0;

        return [
            Stat::make('Monthly Revenue', $this->formatAmount($monthlyRevenue))
                ->description('Total business income this month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),
                
            Stat::make('Profit Margin', round($profitMargin, 1) . '%')
                ->description($profitMargin > 15 ? 'Healthy margin' : 'Consider optimizing costs')
                ->descriptionIcon('heroicon-m-calculator')
                ->color($profitMargin > 15 ? 'success' : 'warning'),
                
            Stat::make('Net Profit', $this->formatAmount($profit))
                ->description('Revenue minus expenses')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($profit > 0 ? 'success' : 'danger'),
        ];
    }

    private function getFreelancerStats(): array
    {
        $projectCount = auth()->user()->employmentHistory()
            ->where('employment_type', 'freelance')
            ->whereYear('start_date', now()->year)
            ->count();

        $monthlyIncome = Income::where('user_id', auth()->id())
            ->whereMonth('entry_date', now()->month)
            ->sum('amount');

        return [
            Stat::make('Projects This Year', $projectCount)
                ->description('Freelance projects completed')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
                
            Stat::make('Monthly Income', $this->formatAmount($monthlyIncome))
                ->description('Earnings this month')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
                
            Stat::make('Freelancer Tip', 'Diversify Clients')
                ->description('Maintain multiple income streams')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info'),
        ];
    }

    private function getUnemployedStats(): array
    {
        $expenses = Expense::where('user_id', auth()->id())
            ->whereMonth('entry_date', now()->month)
            ->sum('amount');

        $lastJob = auth()->user()->employmentHistory()
            ->orderBy('end_date', 'desc')
            ->first();

        return [
            Stat::make('Monthly Expenses', $this->formatAmount($expenses))
                ->description('Current spending to manage')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning'),
                
            Stat::make('Last Employment', $lastJob ? $lastJob->company_name : 'Not recorded')
                ->description($lastJob ? 'Ended ' . $lastJob->end_date?->diffForHumans() : 'Add your work history')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
                
            Stat::make('Focus Area', 'Emergency Fund')
                ->description('Maintain 3-6 months of expenses saved')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('success'),
        ];
    }

    private function calculateMonthlySalary($employment): float
    {
        if (!$employment->salary) return 0;

        return match($employment->salary_frequency) {
            'hourly' => $employment->salary * 160, // 40 hours/week * 4 weeks
            'monthly' => $employment->salary,
            'yearly' => $employment->salary / 12,
            default => $employment->salary
        };
    }

    private function formatAmount($value)
    {
        if (!$value) return '$0';
        $currency = auth()->user()->currency ? auth()->user()->currency : 'usd';
        return Currency::find($currency)->format($value, true);
    }
}