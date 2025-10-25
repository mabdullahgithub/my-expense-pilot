<?php

namespace App\Filament\Widgets;

use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Activity;
use Squire\Models\Currency;

class RecentActivity extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function isCollapsible(): bool
    {
        return true;
    }

    protected function formatAmount($value)
    {
        $currency = auth()->user()->currency ? auth()->user()->currency : 'usd';
        return Currency::find($currency)->format($value, true);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Activity::latest()->take(10))
            ->columns([
                Columns\TextColumn::make('subject.title')
                    ->label('Title'),
                Columns\TextColumn::make('subject_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'expense' => 'Expense',
                        'income' => 'Income',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'expense' => 'danger',
                        'income' => 'success',
                        default => 'gray',
                    })
                    ->label('Type'),
                Columns\TextColumn::make('subject.category.name')
                    ->label('Category'),
                Columns\TextColumn::make('subject.amount')
                    ->formatStateUsing(fn ($record): string => $this->formatAmount($record->subject->amount))
                    ->label('Amount'),
                Columns\TextColumn::make('subject.entry_date')
                    ->label('Date')
                    ->date(),
            ])
            ->paginated(false);
    }
}
