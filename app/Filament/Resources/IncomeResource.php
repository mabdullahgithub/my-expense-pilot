<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Models\Income;
use App\Models\Category;
use App\Models\EmploymentHistory;
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
                Section::make('Income Information')
                    ->columns(2)
                    ->schema([
                        Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('$'),

                        Components\Select::make('category_id')
                            ->label('Category')
                            ->options(Category::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->preload(),

                        Components\DateTimePicker::make('entry_date')
                            ->label('Entry Date')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),

                Section::make('Income Source')
                    ->schema([
                        Components\Select::make('employment_history_id')
                            ->label('Employment Position')
                            ->options(function () {
                                return EmploymentHistory::where('user_id', auth()->id())
                                    ->get()
                                    ->mapWithKeys(function ($employment) {
                                        $profileLabel = $employment->getProfileTypeLabel();
                                        $label = "[{$profileLabel}] {$employment->company_name} - {$employment->position}";
                                        if ($employment->is_current) {
                                            $label .= ' (Current)';
                                        }
                                        return [$employment->id => $label];
                                    });
                            })
                            ->searchable()
                            ->nullable()
                            ->helperText('Select the position/employment from which this income was received (optional)')
                            ->preload(),
                    ]),
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
                Columns\TextColumn::make('employmentHistory.profile_type')
                    ->label('Profile Type')
                    ->formatStateUsing(fn (?string $state): string => $state ? match ($state) {
                        'student' => 'Student',
                        'employee' => 'Employee',
                        'business_owner' => 'Business Owner',
                        'freelancer' => 'Freelancer',
                        default => $state,
                    } : '-')
                    ->badge()
                    ->color(fn (?string $state): string => $state ? match ($state) {
                        'student' => 'info',
                        'employee' => 'success',
                        'business_owner' => 'warning',
                        'freelancer' => 'purple',
                        default => 'gray',
                    } : 'gray')
                    ->sortable()
                    ->toggleable(),
                Columns\TextColumn::make('employmentHistory.company_name')
                    ->label('Company')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),
                Columns\TextColumn::make('employmentHistory.position')
                    ->label('Position')
                    ->sortable()
                    ->toggleable()
                    ->placeholder('-'),
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