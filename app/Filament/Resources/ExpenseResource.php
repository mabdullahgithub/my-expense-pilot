<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Models\Expense;
use App\Models\Category;
use Filament\Schemas\Components as FormComponents;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-arrow-trending-down';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Income/Expense';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FormComponents\Section::make()
                    ->schema([
                        FormComponents\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        FormComponents\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        FormComponents\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        FormComponents\Select::make('category_id')
                            ->label('Category')
                            ->options(Category::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        FormComponents\DateTimePicker::make('entry_date')
                            ->required()
                            ->default(now()),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('amount')
                    ->money('USD')
                    ->sortable(),
                Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Columns\TextColumn::make('entry_date')
                    ->dateTime()
                    ->sortable(),
                Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('entry_date', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
