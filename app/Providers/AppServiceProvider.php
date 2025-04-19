<?php

declare(strict_types=1);

namespace App\Providers;

use App\Service\CustomWebhookService;
use App\Service\GithubWebhookService;
use App\Service\StripeWebhookService;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $classes = $this->app->when([
            GithubWebhookService::class,
            StripeWebhookService::class,
            CustomWebhookService::class,
        ]);

        $classes->needs('$payload')->give(fn () => Json::decode($this->app['request']->getContent()));
        $classes->needs('$headers')->give(fn () => $this->app['request']->headers->all());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::unguard();
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
