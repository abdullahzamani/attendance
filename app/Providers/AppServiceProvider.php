<?php

namespace App\Providers;

use App\Http\Middleware\AuthorizeBoard;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Re-check board access on the board's polling requests, not just the first page load.
        Livewire::addPersistentMiddleware([AuthorizeBoard::class]);
    }
}
