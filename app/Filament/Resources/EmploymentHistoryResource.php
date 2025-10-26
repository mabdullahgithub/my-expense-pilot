<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmploymentHistoryResource\Pages;
use App\Filament\Resources\EmploymentHistoryResource\RelationManagers;
use App\Models\EmploymentHistory;
use App\Models\User;
use Filament\Forms\Components;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions;
use Filament\Tables\Columns;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class EmploymentHistoryResource extends Resource
{
    protected static ?string $model = EmploymentHistory::class;

    protected static ?string $navigationLabel = 'Employment History';

    protected static ?string $pluralModelLabel = 'Employment History';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-briefcase';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Account';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Employment Details')
                    ->schema([
                        Components\Select::make('profile_type')
                            ->label('Profile Type')
                            ->options(function () {
                                $userProfileTypes = auth()->user()->profile_type ?? [];
                                $allOptions = User::getProfileTypeOptions();

                                // Filter to only show options that match user's profile types
                                return array_intersect_key($allOptions, array_flip($userProfileTypes));
                            })
                            ->required()
                            ->helperText('This employment belongs to which profile type?')
                            ->columnSpanFull(),

                        Components\TextInput::make('company_name')
                            ->label('Company Name')
                            ->required()
                            ->maxLength(255),

                        Components\TextInput::make('position')
                            ->label('Job Title/Position')
                            ->required()
                            ->maxLength(255),

                        Components\Select::make('employment_type')
                            ->label('Employment Type')
                            ->options([
                                'full_time' => 'Full Time',
                                'part_time' => 'Part Time',
                                'contract' => 'Contract',
                                'internship' => 'Internship',
                                'freelance' => 'Freelance',
                            ])
                            ->required(),

                        Components\Toggle::make('is_current')
                            ->label('Current Position')
                            ->default(false)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Salary Information')
                    ->schema([
                        Components\TextInput::make('salary')
                            ->label('Salary')
                            ->numeric()
                            ->prefix('$')
                            ->nullable(),
                        
                        Components\Select::make('salary_frequency')
                            ->label('Salary Frequency')
                            ->options([
                                'hourly' => 'Per Hour',
                                'monthly' => 'Per Month',
                                'yearly' => 'Per Year',
                            ])
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Duration')
                    ->schema([
                        Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false),

                        Components\DatePicker::make('end_date')
                            ->label('End Date')
                            ->nullable()
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Components\Textarea::make('description')
                            ->label('Job Description')
                            ->rows(4)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('profile_type')
                    ->label('Profile Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'student' => 'Student',
                        'employee' => 'Employee',
                        'business_owner' => 'Business Owner',
                        'freelancer' => 'Freelancer',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'student' => 'info',
                        'employee' => 'success',
                        'business_owner' => 'warning',
                        'freelancer' => 'purple',
                        default => 'gray',
                    })
                    ->sortable(),

                Columns\TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                Columns\TextColumn::make('position')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),
                
                Columns\TextColumn::make('employment_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'full_time' => 'Full Time',
                        'part_time' => 'Part Time',
                        'contract' => 'Contract',
                        'internship' => 'Internship',
                        'freelance' => 'Freelance',
                        default => $state,
                    })
                    ->sortable(),
                
                Columns\TextColumn::make('salary')
                    ->label('Salary')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(),

                Columns\TextColumn::make('incomes_sum_amount')
                    ->label('Total Income Earned')
                    ->sum('incomes', 'amount')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('$0.00'),

                Columns\TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                
                Columns\TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->placeholder('Current')
                    ->sortable(),
                
                Columns\IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean()
                    ->sortable(),
            ])
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
            ])
            ->defaultSort('start_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\IncomesRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmploymentHistories::route('/'),
            'create' => Pages\CreateEmploymentHistory::route('/create'),
            'edit' => Pages\EditEmploymentHistory::route('/{record}/edit'),
        ];
    }
}