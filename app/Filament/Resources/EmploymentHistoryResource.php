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
use Filament\Infolists\Infolist;
use Filament\Infolists\Components as InfoComponents;
use Filament\Support\Enums\FontWeight;
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
                    ->columns(2)
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
                    ]),

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

                Section::make('Additional Information')
                    ->schema([
                        Components\Textarea::make('description')
                            ->label('Job Description')
                            ->rows(4)
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Columns\Layout\Stack::make([
                    // Header Section - Company & Position
                    Columns\Layout\Split::make([
                        Columns\Layout\Stack::make([
                            Columns\TextColumn::make('company_name')
                                ->weight(FontWeight::Bold)
                                ->size('lg')
                                ->icon('heroicon-o-building-office-2')
                                ->iconColor('primary')
                                ->searchable()
                                ->sortable(),

                            Columns\TextColumn::make('position')
                                ->size('md')
                                ->color('gray')
                                ->weight(FontWeight::Medium)
                                ->icon('heroicon-o-briefcase')
                                ->iconColor('gray')
                                ->searchable()
                                ->sortable(),
                        ])->space(1),

                        Columns\TextColumn::make('is_current')
                            ->formatStateUsing(fn ($state): string => $state ? 'Current' : '')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-check-badge')
                            ->visible(fn ($state): bool => (bool) $state)
                            ->grow(false),
                    ])->from('md'),

                    Columns\Layout\View::make('filament.tables.columns.divider'),

                    // Profile & Employment Type
                    Columns\Layout\Split::make([
                        Columns\TextColumn::make('profile_type')
                            ->label('Profile')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'student' => 'Student',
                                'employee' => 'Employee',
                                'business_owner' => 'Business Owner',
                                'freelancer' => 'Freelancer',
                                default => $state,
                            })
                            ->badge()
                            ->size('md')
                            ->icon(fn (string $state): string => match ($state) {
                                'student' => 'heroicon-o-academic-cap',
                                'employee' => 'heroicon-o-building-office',
                                'business_owner' => 'heroicon-o-building-storefront',
                                'freelancer' => 'heroicon-o-user',
                                default => 'heroicon-o-user',
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'student' => 'info',
                                'employee' => 'success',
                                'business_owner' => 'warning',
                                'freelancer' => 'purple',
                                default => 'gray',
                            }),

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
                            ->badge()
                            ->size('md')
                            ->color('gray')
                            ->icon('heroicon-o-clock'),
                    ]),

                    Columns\Layout\View::make('filament.tables.columns.divider'),

                    // Duration Section
                    Columns\Layout\Grid::make(2)
                        ->schema([
                            Columns\Layout\Stack::make([
                                Columns\TextColumn::make('start_date')
                                    ->label('Start Date')
                                    ->date('M d, Y')
                                    ->icon('heroicon-o-calendar')
                                    ->iconColor('success')
                                    ->weight(FontWeight::SemiBold)
                                    ->sortable(),
                            ]),

                            Columns\Layout\Stack::make([
                                Columns\TextColumn::make('end_date')
                                    ->label('End Date')
                                    ->date('M d, Y')
                                    ->icon('heroicon-o-calendar-days')
                                    ->iconColor('danger')
                                    ->placeholder('Present')
                                    ->weight(FontWeight::SemiBold)
                                    ->sortable(),
                            ]),
                        ]),

                    Columns\Layout\View::make('filament.tables.columns.divider'),

                    // Financial Overview
                    Columns\Layout\Split::make([
                        Columns\Layout\Stack::make([
                            Columns\TextColumn::make('salary')
                                ->label('Salary')
                                ->formatStateUsing(function ($state, $record): string {
                                    if (!$state) {
                                        return 'Not specified';
                                    }
                                    $salary = '$' . number_format($state, 2);
                                    if ($record->salary_frequency) {
                                        $frequency = match ($record->salary_frequency) {
                                            'hourly' => '/hr',
                                            'monthly' => '/mo',
                                            'yearly' => '/yr',
                                            default => '',
                                        };
                                        $salary .= ' ' . $frequency;
                                    }
                                    return $salary;
                                })
                                ->icon('heroicon-o-banknotes')
                                ->iconColor('warning')
                                ->color(fn ($state): string => $state ? 'warning' : 'gray')
                                ->weight(fn ($state): FontWeight => $state ? FontWeight::Bold : FontWeight::Medium)
                                ->size('sm'),
                        ]),

                        Columns\Layout\Stack::make([
                            Columns\TextColumn::make('incomes_sum_amount')
                                ->label('Total Earned')
                                ->sum('incomes', 'amount')
                                ->money('USD')
                                ->icon('heroicon-o-currency-dollar')
                                ->iconColor('success')
                                ->color('success')
                                ->weight(FontWeight::Bold)
                                ->size('sm')
                                ->placeholder('$0.00'),
                        ]),
                    ]),

                    // Description (if exists)
                    Columns\TextColumn::make('description')
                        ->label('Description')
                        ->limit(120)
                        ->wrap()
                        ->size('xs')
                        ->color('gray')
                        ->icon('heroicon-o-document-text')
                        ->iconColor('gray')
                        ->visible(fn ($state): bool => !empty($state))
                        ->extraAttributes(['class' => 'italic']),

                ])->space(2),
            ])
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->paginated(false)
            ->actions([
                Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Employment'),
                Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Employment'),
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