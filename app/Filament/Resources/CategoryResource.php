<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Schemas\Components as FormComponents;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

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
                FormComponents\Section::make()
                    ->schema([
                        FormComponents\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        FormComponents\Toggle::make('is_active')
                            ->label('Active')
                            ->inline(false)
                            ->required(),
                    ])
                    ->columns([
                        'sm' => 1,
                    ])
                    ->columnSpan(2),
                FormComponents\Section::make()
                    ->schema([
                        FormComponents\Placeholder::make('created_at')
                            ->label('Created at')
                            ->content(fn (?Category $record): string => $record ? $record->created_at->diffForHumans() : '-'),
                        FormComponents\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(fn (?Category $record): string => $record ? $record->updated_at->diffForHumans() : '-'),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Columns\IconColumn::make('is_active')->label('Status')
                    ->boolean()
                    ->searchable()
                    ->sortable(),
                Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
