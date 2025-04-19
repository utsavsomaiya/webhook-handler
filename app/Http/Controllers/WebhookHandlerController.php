<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Service\CustomWebhookService;
use App\Service\GithubWebhookService;
use App\Service\StripeWebhookService;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class WebhookHandlerController extends Controller
{
    public function __invoke(Request $request): Response
    {
        if (! $request->isJson()) {
            return new Response('Invalid request', HttpResponse::HTTP_BAD_REQUEST);
        }

        Log::info('Incoming webhook', [
            'headers' => $headers = $request->headers->all(),
            'payload' => $payload = Json::decode($request->getContent()),
        ]);

        if (blank($payload)) {
            return new Response('Empty payload', HttpResponse::HTTP_BAD_REQUEST);
        }

        return match (true) {
            /**
             * @see https://docs.github.com/en/webhooks/webhook-events-and-payloads#example-webhook-delivery
             */
            $headers['X-GitHub-Event'] ?? false => App::call(GithubWebhookService::class),
            /**
             * @see http://github.com/laravel/cashier-stripe/blob/15.x/src/Http/Middleware/VerifyWebhookSignature.php#L26
             */
            $headers['Stripe-Signature'] ?? false => App::call(StripeWebhookService::class),
            default => App::call(CustomWebhookService::class),
        };
    }
}
