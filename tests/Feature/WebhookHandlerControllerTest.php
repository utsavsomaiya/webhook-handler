<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CustomWebhook;
use App\Models\StripeTransaction;
use App\Service\StripeWebhookService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class WebhookHandlerControllerTest extends TestCase
{
    #[Test]
    public function it_can_only_allow_json_request(): void
    {
        $this->post(route('webhook.handler'))
            ->assertBadRequest()
            ->assertSee('Invalid request');
    }

    #[Test]
    public function it_can_handle_empty_payload(): void
    {
        $this->postJson(route('webhook.handler'), [])
            ->assertBadRequest()
            ->assertSee('Empty payload');
    }

    #[Test]
    public function it_can_handle_github_webhook(): void
    {
        $this->postJson(route('webhook.handler'), [
            'commits' => [
                [
                    'id' => '1234567890',
                    'message' => 'Test commit message',
                    'author' => [
                        'name' => 'Utsav Somaiya',
                    ],
                ],
            ],
        ], [
            'x-github-event' => 'push',
        ])
            ->assertOk();
    }

    #[Test]
    public function it_can_handle_stripe_webhook(): void
    {
        $this->postJson(route('webhook.handler'), [
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_1234567890',
                    'amount_received' => 1000,
                    'currency' => 'usd',
                    'status' => 'succeeded',
                ],
            ],
        ], [
            'Stripe-Signature' => 't=1234567890,v1=1234567890,v0=1234567890',
        ])
            ->assertOk();

        $this->assertDatabaseCount(StripeTransaction::class, 1)
            ->assertDatabaseHas(StripeTransaction::class, [
                'amount' => 1000,
                'currency' => 'usd',
                'status' => 'succeeded',
                'meta->payload->type' => 'payment_intent.succeeded',
            ]);
    }

    #[Test]
    public function it_can_handle_custom_webhook(): void
    {
        $this->postJson(route('webhook.handler'), ['foo' => 'bar'])
            ->assertOk();

        $this->assertDatabaseCount(CustomWebhook::class, 1)
            ->assertDatabaseHas(CustomWebhook::class, [
                'data->payload->foo' => 'bar',
            ]);
    }
}
