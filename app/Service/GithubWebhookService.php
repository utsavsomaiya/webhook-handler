<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\GithubCommit;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class GithubWebhookService
{
    public function __construct(
        private array $headers,
        private array $payload,
    ) {}

    public function __invoke(): Response
    {
        // Need to create repository webhook: https://docs.github.com/en/webhooks/types-of-webhooks#repository-webhooks
        // Ref: https://docs.github.com/en/webhooks/webhook-events-and-payloads#push

        // TODO: Check if the webhook is valid: https://docs.github.com/en/webhooks/using-webhooks/validating-webhook-deliveries#validating-webhook-deliveries

        if ($this->headers['X-GitHub-Event'] === 'push') {
            $commits = collect($this->payload['commits'] ?? [])
                ->map(fn (array $commit): array => [
                    'commit_id' => $commit['id'],
                    'message' => $commit['message'],
                    'author' => $commit['author']['name'],
                    // TODO: May be we need to put it into another table and link it with this one
                    'meta' => Json::encode([
                        'headers' => $this->headers,
                        'payload' => $this->payload,
                    ], JSON_PRETTY_PRINT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->values()
                ->all();

            if (blank($commits)) {
                Log::error('Github webhook push event has no commits', [
                    'headers' => $this->headers,
                    'payload' => $this->payload,
                ]);
            }

            GithubCommit::query()->insert($commits);
        } else {
            Log::error('Github webhook event is not supported', [
                'headers' => $this->headers,
                'payload' => $this->payload,
            ]);
        }

        return new Response('Github webhook received', HttpResponse::HTTP_OK);
    }
}
