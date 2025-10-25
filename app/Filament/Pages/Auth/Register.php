<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Pages\SimplePage;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\RenderHook;
use Filament\View\PanelsRenderHook;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Illuminate\Support\HtmlString;

/**
 * @property-read Action $loginAction
 * @property-read Schema $form
 */
class Register extends SimplePage
{
    use WithRateLimiting;

    protected string $view = 'register';

    public ?array $data = [];

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->callHook('beforeFill');

        $this->form->fill();

        $this->callHook('afterFill');
    }

    public function register(): void
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->addError('email', __('filament::register.messages.throttled', [
                'seconds' => $exception->secondsUntilAvailable,
                'minutes' => ceil($exception->secondsUntilAvailable / 60),
            ]));

            return;
        }

        $this->callHook('beforeValidate');

        $data = $this->form->getState();

        $this->callHook('afterValidate');

        $this->callHook('beforeRegister');

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $this->callHook('afterRegister');

        event(new Registered($user));

        Filament::auth()->login($user);

        session()->regenerate();

        redirect()->intended(Filament::getUrl());
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('register.fields.name.label'))
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                TextInput::make('email')
                    ->label(__('register.fields.email.label'))
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(User::class),
                TextInput::make('password')
                    ->label(__('register.fields.password.label'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->minLength(8)
                    ->same('passwordConfirmation')
                    ->dehydrateStateUsing(fn ($state) => $state),
                TextInput::make('passwordConfirmation')
                    ->label(__('register.fields.passwordConfirmation.label'))
                    ->password()
                    ->revealable()
                    ->required()
                    ->dehydrated(false),
            ]);
    }

    public function loginAction(): Action
    {
        return Action::make('login')
            ->link()
            ->label(__('register.links.login.label'))
            ->url(route('filament.admin.auth.login'));
    }

    public function getTitle(): string | Htmlable
    {
        return __('register.title');
    }

    public function getHeading(): string | Htmlable | null
    {
        return __('register.heading');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getRegisterFormAction(),
        ];
    }

    public function getRegisterFormAction(): Action
    {
        return Action::make('register')
            ->label(__('register.buttons.submit.label'))
            ->submit('register');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    public function getSubheading(): string | Htmlable | null
    {
        return null;
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                RenderHook::make(PanelsRenderHook::AUTH_REGISTER_FORM_BEFORE),
                $this->getFormContentComponent(),
                RenderHook::make(PanelsRenderHook::AUTH_REGISTER_FORM_AFTER),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('register')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
                Actions::make([$this->loginAction()])
                    ->alignment('center')
                    ->fullWidth(false)
                    ->key('login-action'),
            ]);
    }

    public function getFormActionsAlignment(): string
    {
        return 'start';
    }
}
