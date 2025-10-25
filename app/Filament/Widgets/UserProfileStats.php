<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\EmploymentHistory;
use App\Models\Income;
use App\Models\Expense;
use Squire\Models\Currency;

class UserProfileStats extends BaseWidget
{
    protected static ?int $sort = 0;

    protected function formatAmount($value)
    {
        $currency = auth()->user()->currency ? auth()->user()->currency : 'usd';
        return Currency::find($currency)->format($value, true);
    }

    protected function getStats(): array
    {
        $user = auth()->user();
        $profileTypes = is_array($user->profile_type) ? $user->profile_type : [];
        $profileTypeLabels = implode(', ', $user->getProfileTypeLabels());

        // Get current employment
        $currentEmployment = $user->currentEmployment()->first();

        // Calculate stats based on profile type
        $stats = [];

        // Profile Type Stat - shows all selected profile types
        $stats[] = Stat::make('Profile Type', $profileTypeLabels)
            ->description('Your profile type' . (count($profileTypes) > 1 ? 's' : ''))
            ->descriptionIcon($this->getProfileIcon($profileTypes))
            ->color($this->getProfileColor($profileTypes));

        // Employment/Salary Stats based on primary profile type (first one)
        $primaryType = !empty($profileTypes) ? $profileTypes[0] : null;

        if ($primaryType === 'student') {
            $stats[] = $this->getStudentStats();
        } elseif ($primaryType === 'employee') {
            $stats[] = $this->getEmployeeStats($currentEmployment);
        } elseif ($primaryType === 'business_owner') {
            $stats[] = $this->getBusinessOwnerStats();
        } elseif ($primaryType === 'freelancer') {
            $stats[] = $this->getFreelancerStats();
        } else {
            $stats[] = $this->getEmployeeStats($currentEmployment);
        }

        // Career Progress
        $totalJobs = $user->employmentHistory()->count();
        $stats[] = Stat::make('Career History', $totalJobs . ' positions')
            ->description('Total employment records')
            ->descriptionIcon('heroicon-m-briefcase')
            ->color('info');

        return $stats;
    }

    private function getStudentStats(): Stat
    {
        $partTimeJobs = auth()->user()->employmentHistory()
            ->whereIn('employment_type', ['part_time', 'internship'])
            ->count();
            
        return Stat::make('Part-time Experience', $partTimeJobs . ' positions')
            ->description('Part-time jobs & internships')
            ->descriptionIcon('heroicon-m-academic-cap')
            ->color('primary');
    }

    private function getEmployeeStats($currentEmployment): Stat
    {
        if ($currentEmployment && $currentEmployment->salary) {
            $monthlySalary = $this->calculateMonthlySalary($currentEmployment);
            return Stat::make('Current Salary', $this->formatAmount($monthlySalary))
                ->description('Monthly at ' . $currentEmployment->company_name)
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success');
        }
        
        return Stat::make('Current Position', $currentEmployment ? $currentEmployment->position : 'Not specified')
            ->description($currentEmployment ? $currentEmployment->company_name : 'No current employment')
            ->descriptionIcon('heroicon-m-building-office')
            ->color($currentEmployment ? 'success' : 'warning');
    }

    private function getBusinessOwnerStats(): Stat
    {
        $totalRevenue = Income::where('user_id', auth()->id())->sum('amount');
        return Stat::make('Business Revenue', $this->formatAmount($totalRevenue))
            ->description('Total business income')
            ->descriptionIcon('heroicon-m-chart-bar')
            ->color('success');
    }

    private function getFreelancerStats(): Stat
    {
        $freelanceJobs = auth()->user()->employmentHistory()
            ->where('employment_type', 'freelance')
            ->count();
            
        return Stat::make('Freelance Projects', $freelanceJobs . ' projects')
            ->description('Completed freelance work')
            ->descriptionIcon('heroicon-m-computer-desktop')
            ->color('primary');
    }

    private function getUnemployedStats(): Stat
    {
        $lastJob = auth()->user()->employmentHistory()
            ->orderBy('end_date', 'desc')
            ->first();
            
        $description = $lastJob 
            ? 'Last: ' . $lastJob->company_name 
            : 'Ready for opportunities';
            
        return Stat::make('Job Status', 'Seeking Employment')
            ->description($description)
            ->descriptionIcon('heroicon-m-magnifying-glass')
            ->color('warning');
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

    private function getProfileIcon($profileTypes): string
    {
        // Use first profile type for icon
        $type = is_array($profileTypes) && !empty($profileTypes) ? $profileTypes[0] : null;

        return match($type) {
            'student' => 'heroicon-m-academic-cap',
            'employee' => 'heroicon-m-briefcase',
            'business_owner' => 'heroicon-m-building-storefront',
            'freelancer' => 'heroicon-m-computer-desktop',
            default => 'heroicon-m-user'
        };
    }

    private function getProfileColor($profileTypes): string
    {
        // Use first profile type for color
        $type = is_array($profileTypes) && !empty($profileTypes) ? $profileTypes[0] : null;

        return match($type) {
            'student' => 'primary',
            'employee' => 'success',
            'business_owner' => 'warning',
            'freelancer' => 'info',
            default => 'gray'
        };
    }
}