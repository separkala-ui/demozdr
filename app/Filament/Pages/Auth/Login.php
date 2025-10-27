<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getHeading(): string | Htmlable
    {
        return __('ورود به حساب کاربری');
    }

    protected function getFormActions(): array
    {
        $actions = parent::getFormActions();
        
        // Add back to main dashboard button
        $actions[] = \Filament\Actions\Action::make('backToDashboard')
            ->label('بازگشت به پنل اصلی')
            ->icon('heroicon-o-arrow-left')
            ->color('gray')
            ->url(route('admin.dashboard'))
            ->outlined();

        return $actions;
    }
}

