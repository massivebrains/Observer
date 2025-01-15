<?php declare(strict_types=1);

namespace App\Providers;

use App\Services\FingerprintService;
use App\Services\Monolith\MonolithHttpService;
use App\Services\RabbitMQ\RabbitMQService;
use Context;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // calling as early as possible to have the fingerprint in the context
        $this->addFingerprintToContext();

        $this->app->singleton('rabbitmq', fn () => new RabbitMQService(config('services.rabbitmq')));
        $this->app->singleton(MonolithHttpService::class, fn () => new MonolithHttpService(config('observer.auth-token')));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Add fingerprint to the context.
     */
    private function addFingerprintToContext(): void
    {
        // In case there is a context information coming from dehydrated context
        $fingerprint = Context::get('fingerprint') ?: (new FingerprintService)->get();

        Context::add('fingerprint', $fingerprint);
    }
}
