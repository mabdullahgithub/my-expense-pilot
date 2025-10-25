<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Placeholder;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->before(function () {
                    $record = $this->getRecord();
                    if ($record->incomes()->count() > 0 || $record->expenses()->count() > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('Cannot delete category')
                            ->body('This category has associated transactions.')
                            ->danger()
                            ->send();
                        return false;
                    }
                }),
        ];
    }

    public function schema(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Category Information')
                    ->schema([
                        Placeholder::make('name')
                            ->label('Name')
                            ->content(fn ($record) => $record->name ?? 'N/A'),
                        
                        Placeholder::make('description')
                            ->label('Description')
                            ->content(fn ($record) => $record->description ?? 'No description provided')
                            ->columnSpanFull(),
                        
                        Placeholder::make('color')
                            ->label('Color')
                            ->content(function ($record) {
                                $color = $record->color ?? '#3b82f6';
                                return '<div style="width: 24px; height: 24px; background-color: ' . $color . '; border-radius: 4px; display: inline-block;"></div> ' . $color;
                            }),
                        
                        Placeholder::make('is_active')
                            ->label('Status')
                            ->content(fn ($record) => $record->is_active ? '✅ Active' : '❌ Inactive'),
                    ])
                    ->columns(2),

                Section::make('Usage Statistics')
                    ->schema([
                        Placeholder::make('incomes_count')
                            ->label('Total Incomes')
                            ->content(fn ($record) => number_format($record->incomes()->count())),
                        
                        Placeholder::make('expenses_count')
                            ->label('Total Expenses')
                            ->content(fn ($record) => number_format($record->expenses()->count())),
                        
                        Placeholder::make('total_income_amount')
                            ->label('Total Income Amount')
                            ->content(fn ($record) => '$' . number_format($record->incomes()->sum('amount'), 2)),
                        
                        Placeholder::make('total_expense_amount')
                            ->label('Total Expense Amount')
                            ->content(fn ($record) => '$' . number_format($record->expenses()->sum('amount'), 2)),
                        
                        Placeholder::make('net_amount')
                            ->label('Net Amount')
                            ->content(function ($record) {
                                $income = $record->incomes()->sum('amount');
                                $expense = $record->expenses()->sum('amount');
                                $net = $income - $expense;
                                return '$' . number_format($net, 2);
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Timestamps')
                    ->schema([
                        Placeholder::make('created_at')
                            ->label('Created At')
                            ->content(fn ($record) => $record->created_at->format('M d, Y g:i A')),
                        
                        Placeholder::make('updated_at')
                            ->label('Last Updated')
                            ->content(fn ($record) => $record->updated_at->format('M d, Y g:i A')),
                    ])
                    ->columns(2),
            ]);
    }
}