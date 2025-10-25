<?php

namespace App\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label('Don\'t have an account? Register')
            ->url(route('filament.admin.auth.register'));
    }

    public function forgotPasswordAction(): Action
    {
        return Action::make('forgotPassword')
            ->link()
            ->label('Forgot password?')
            ->url(route('filament.admin.auth.password-reset.request'));
    }

    public function getSubheading(): string | Htmlable | null
    {
        return null;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    public function getAuthenticateFormAction(): Action
    {
        return parent::getAuthenticateFormAction();
    }

    protected function getFooterWidgets(): array
    {
        return [];
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
                \Filament\Schemas\Components\RenderHook::make(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE),
                $this->getFormContentComponent(),
                \Filament\Schemas\Components\RenderHook::make(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER),
            ]);
    }

    public function getFormContentComponent(): \Filament\Schemas\Components\Component
    {
        return \Filament\Schemas\Components\Form::make([\Filament\Schemas\Components\EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                \Filament\Schemas\Components\Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
                \Filament\Schemas\Components\Actions::make([$this->registerAction()])
                    ->alignment('center')
                    ->fullWidth(false)
                    ->key('register-action'),
            ]);
    }
}
