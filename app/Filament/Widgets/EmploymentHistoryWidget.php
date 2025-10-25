<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\EmploymentHistory;
use Squire\Models\Currency;

class EmploymentHistoryWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public function getHeading(): string
    {
        return 'Employment History';
    }

    protected int | string | array $columnSpan = 'full';

    protected function formatAmount($value)
    {
        if (!$value) return 'Not specified';
        $currency = auth()->user()->currency ? auth()->user()->currency : 'usd';
        return Currency::find($currency)->format($value, true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                EmploymentHistory::where('user_id', auth()->id())
                    ->orderBy('is_current', 'desc')
                    ->orderBy('start_date', 'desc')
            )
            ->columns([
                Columns\TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->weight('bold'),
                
                Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable(),
                
                Columns\TextColumn::make('employment_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'internship' => 'Internship',
                        'freelance' => 'Freelance',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'full_time' => 'success',
                        'part_time' => 'warning',
                        'contract' => 'info',
                        'internship' => 'gray',
                        'freelance' => 'primary',
                        default => 'gray',
                    }),
                
                Columns\TextColumn::make('salary')
                    ->label('Salary')
                    ->formatStateUsing(function ($record): string {
                        if (!$record->salary) return 'Not specified';
                        
                        $amount = $this->formatAmount($record->salary);
                        $frequency = match($record->salary_frequency) {
                            'hourly' => '/hr',
                            'monthly' => '/month',
                            'yearly' => '/year',
                            default => ''
                        };
                        
                        return $amount . $frequency;
                    }),
                
                Columns\TextColumn::make('duration')
                    ->label('Duration')
                    ->formatStateUsing(function ($record): string {
                        return $record->getDuration();
                    }),
                
                Columns\IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('No Employment History')
            ->emptyStateDescription('Add your first employment record to track your career journey.')
            ->emptyStateIcon('heroicon-o-briefcase');
    }
}