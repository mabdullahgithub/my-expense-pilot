<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use App\Models\Category;
use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-arrow-trending-up';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Income/Expense';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('$'),
                        Components\Select::make('category_id')
                            ->label('Category')
                            ->options(Category::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        Components\DateTimePicker::make('entry_date')
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
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }
}