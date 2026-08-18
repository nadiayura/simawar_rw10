<?php

namespace App\Providers\Filament;

use App\Filament\Warga\Auth\WargaLogin;
use App\Filament\Warga\Auth\WargaRegister;
use App\Filament\Warga\Widgets\WargaOverviewWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class WargaPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('warga')
            ->path('warga')
            ->login(WargaLogin::class)
            ->registration(WargaRegister::class)
            ->passwordReset()
            ->homeUrl('/warga')
            ->brandName('SIMAWAR 10 | WARGA')
            ->favicon('/storage/logo/logoutama.png')
            ->colors([
                'primary' => Color::hex('#8789b0'),
            ])
            ->discoverResources(in: app_path('Filament/Warga/Resources'), for: 'App\Filament\Warga\Resources')
            ->discoverPages(in: app_path('Filament/Warga/Pages'), for: 'App\Filament\Warga\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Warga/Widgets'), for: 'App\Filament\Warga\Widgets')
            ->widgets([
                WargaOverviewWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::AUTH_REGISTER_FORM_AFTER,
                fn (): string => Blade::render('<script>document.addEventListener(\"DOMContentLoaded\",function(){var b=document.querySelector(\"form button[type=submit], form [type=submit]\");if(b){b.textContent=\"Register\";}var c=document.querySelector(\"a[href$=\'/warga/login\']\");if(c){c.textContent=c.textContent.replace(/sign in/gi,\"login\");}});</script>'),
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
