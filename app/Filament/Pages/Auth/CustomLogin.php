<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login;
use Filament\Forms\Form;

class CustomLogin extends Login
{
    protected static string $layout = 'filament-panels::components.layout.base';

    public function getView(): string
    {
        return 'filament.pages.auth.custom_login';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent()
                    ->extraAttributes([
                        'class' => 'my-4', // Adjusted vertical margin to my-4
                    ]),
            ])
            ->statePath('data');
    }

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return parent::getAuthenticateFormAction()
            ->label('Login')
            ->extraAttributes([
                'class' => 'w-full bg-[#0F4C5C] hover:bg-[#135d70] text-white font-bold py-2 rounded-lg transition duration-200 shadow-md flex justify-center items-center gap-2',
            ]);
    }
}
