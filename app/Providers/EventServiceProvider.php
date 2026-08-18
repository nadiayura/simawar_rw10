<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redirect;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Login::class => [
            // Tambahkan listener untuk event login
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Menangani event login untuk mengarahkan pengguna berdasarkan role
        Event::listen(Login::class, function ($event) {
            $user = $event->user;

            // Jika user memiliki role warga, arahkan ke panel warga
            if ($user->role && $user->role->isWarga()) {
                // Redirect ke panel warga
                return redirect('/warga');
            }

            // Untuk role lain (RT/RW), biarkan default ke panel admin
            return null;
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
