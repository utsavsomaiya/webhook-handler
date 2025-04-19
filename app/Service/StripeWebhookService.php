<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\StripeTransaction;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\WebhookSignature;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class StripeWebhookService
{
    public function __construct(
        protected array $payload,
        protected array $headers,
    ) {}

    public function __invoke(): Response
    {
        $this->verifyWebhookSignature();

        $createFailedOrSuccessTransaction = match ($this->payload['type']) {
            'payment_intent.succeeded' => function (): void {
                StripeTransaction::query()->create([
                    'amount' => $this->payload['data']['object']['amount_received'],
                    'currency' => $this->payload['data']['object']['currency'],
                    'status' => PaymentIntent::STATUS_SUCCEEDED,
                    'meta' => [
                        'headers' => $this->headers,
                        'payload' => $this->payload,
                    ],
                ]);
            },
            default => fn () => Log::warning('Stripe webhook event is not supported', [
                'headers' => $this->headers,
                'payload' => $this->payload,
            ]),
        };

        $createFailedOrSuccessTransaction();

        return new Response('Stripe webhook received', HttpResponse::HTTP_OK);
    }

    private function verifyWebhookSignature(): void
    {
        try {
            if (config('services.stripe.webhook.secret')) {
                WebhookSignature::verifyHeader(
                    json_encode($this->payload, JSON_PRETTY_PRINT),
                    $this->headers['stripe-signature'],
                    config('services.stripe.webhook.secret'),
                    config('services.stripe.webhook.tolerance')
                );
            }
        } catch (SignatureVerificationException $exception) {
            Log::error('Stripe webhook signature verification failed', [
                'exception' => $exception,
                'headers' => $this->headers,
                'payload' => $this->payload,
            ]);

            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        }
    }
}
