<?php

namespace App\Providers;

use App\Services\AuditLogger;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogger::class, fn () => new AuditLogger());
    }
}
