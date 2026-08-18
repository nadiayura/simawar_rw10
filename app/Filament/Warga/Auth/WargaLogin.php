<?php

namespace App\Filament\Warga\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\Login;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class WargaLogin extends Login
{
    public function getHeading(): string
    {
        return 'Log in';
    }

    public function registerAction(): Action
    {
        return Action::make('register')
            ->link()
            ->label('Register here')
            ->url(filament()->getRegistrationUrl());
    }

    public function getPasswordResetAction(): Action
    {
        return Action::make('passwordReset')
            ->link()
            ->label(__('filament-panels::pages/auth/login.actions.request_password_reset.label'))
            ->url(route('warga.password.request'));
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->hint(new HtmlString(Blade::render('<x-filament::link :href="route(\'warga.password.request\')" tabindex="3"> {{ __(\'filament-panels::auth/pages/login.actions.request_password_reset.label\') }} </x-filament::link>')));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }
}
