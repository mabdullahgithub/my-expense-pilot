<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms\Components as FormComponents;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-squares-2x2';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Miscellaneous';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Category Details')
                    ->schema([
                        FormComponents\TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Food, Transportation, Entertainment'),
                        
                        FormComponents\Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Optional description of what this category includes')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                        
                        FormComponents\TextInput::make('color')
                            ->label('Category Color (Hex)')
                            ->default('#3b82f6')
                            ->placeholder('#3b82f6')
                            ->maxLength(7),
                        
                        FormComponents\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),
                
                Section::make('Statistics')
                    ->schema([
                        FormComponents\Placeholder::make('total_incomes')
                            ->label('Total Incomes')
                            ->content(function (?Category $record): string {
                                if (!$record) return '0';
                                return number_format($record->incomes()->count());
                            }),
                        
                        FormComponents\Placeholder::make('total_expenses')
                            ->label('Total Expenses')
                            ->content(function (?Category $record): string {
                                if (!$record) return '0';
                                return number_format($record->expenses()->count());
                            }),
                        
                        FormComponents\Placeholder::make('created_at')
                            ->label('Created')
                            ->content(fn (?Category $record): string => $record ? $record->created_at->diffForHumans() : '-'),
                        
                        FormComponents\Placeholder::make('updated_at')
                            ->label('Updated')
                            ->content(fn (?Category $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hiddenOn('create'),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Category $record): ?string => $record->description)
                    ->wrap(),
                
                Columns\TextColumn::make('color')
                    ->label('Color')
                    ->searchable(false)
                    ->sortable(false),
                
                Columns\TextColumn::make('incomes_count')
                    ->label('Incomes')
                    ->counts('incomes')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                
                Columns\TextColumn::make('expenses_count')
                    ->label('Expenses')
                    ->counts('expenses')
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                
                Columns\TextColumn::make('total_amount')
                    ->label('Net Amount')
                    ->money('USD')
                    ->getStateUsing(function (Category $record): float {
                        $totalIncome = $record->incomes()->sum('amount');
                        $totalExpense = $record->expenses()->sum('amount');
                        return $totalIncome - $totalExpense;
                    })
                    ->color(fn ($state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(false),
                
                Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('is_active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
            ])
            ->actions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No categories found')
            ->emptyStateDescription('Create your first category to organize your income and expenses.')
            ->emptyStateIcon('heroicon-o-squares-2x2')
            ->defaultSort('name', 'asc');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
