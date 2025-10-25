<?php

namespace App\Filament\Resources\EmploymentHistoryResource\RelationManagers;

use App\Models\Category;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class IncomesRelationManager extends RelationManager
{
    protected static string $relationship = 'incomes';

    public function form(Schema $schema): Schema
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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
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
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('entry_date', 'desc');
    }
}
