<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Illuminate\Support\Facades\Hash;
use Squire\Models\Currency;
use Squire\Models\Country;

class Profile extends Page
{
    protected string $view = 'filament.pages.profile';

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Account';
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->data = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'country' => auth()->user()->country,
            'currency' => auth()->user()->currency,
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->model(auth()->user())
            ->statePath('data')
            ->components([
                Section::make('Personal Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->required(),
                    ]),
                Section::make('Configuration')
                    ->schema([
                        Select::make('currency')
                            ->options(Currency::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                        Select::make('country')
                            ->options(Country::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),
                Section::make('Update Password')
                    ->columns(2)
                    ->schema([
                        TextInput::make('current_password')
                            ->label('Current Password')
                            ->password()
                            ->rules(['required_with:new_password'])
                            ->currentPassword()
                            ->autocomplete('off')
                            ->columnSpan(1),
                        Grid::make()
                            ->schema([
                                TextInput::make('new_password')
                                    ->label('New Password')
                                    ->password()
                                    ->rules(['confirmed'])
                                    ->autocomplete('new-password'),
                                TextInput::make('new_password_confirmation')
                                    ->label('Confirm Password')
                                    ->password()
                                    ->rules([
                                        'required_with:new_password',
                                    ])
                                    ->autocomplete('new-password'),
                            ]),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Profile')
                ->submit('save'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function getFormActionsAlignment(): string | Alignment
    {
        return Alignment::Start;
    }

    public function save(): void
    {
        $data = $this->data;

        $state = array_filter([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => !empty($data['new_password']) ? Hash::make($data['new_password']) : null,
            'country' => $data['country'] ?? null,
            'currency' => $data['currency'] ?? null,
        ]);

        auth()->user()->update($state);

        // Reload the form with updated data
        $this->data = [
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'country' => auth()->user()->country,
            'currency' => auth()->user()->currency,
            'current_password' => null,
            'new_password' => null,
            'new_password_confirmation' => null,
        ];

        \Filament\Notifications\Notification::make()
            ->title('Profile updated successfully')
            ->success()
            ->send();
    }

    public function getBreadcrumbs(): array
    {
        return [
            url()->current() => 'Profile',
        ];
    }
}
