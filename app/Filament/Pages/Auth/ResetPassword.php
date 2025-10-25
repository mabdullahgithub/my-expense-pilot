<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Support\Htmlable;

class ResetPassword extends BaseResetPassword
{
    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label('Back to login')
            ->url(route('filament.admin.auth.login'));
    }

    public function getSubheading(): string | Htmlable | null
    {
        return null;
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getFormActionsAlignment(): string
    {
        return 'start';
    }

    public function content(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\RenderHook::make(\Filament\View\PanelsRenderHook::AUTH_PASSWORD_RESET_RESET_FORM_BEFORE),
                $this->getFormContentComponent(),
                \Filament\Schemas\Components\RenderHook::make(\Filament\View\PanelsRenderHook::AUTH_PASSWORD_RESET_RESET_FORM_AFTER),
            ]);
    }

    public function getFormContentComponent(): \Filament\Schemas\Components\Component
    {
        return \Filament\Schemas\Components\Form::make([\Filament\Schemas\Components\EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('resetPassword')
            ->footer([
                \Filament\Schemas\Components\Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }
}
