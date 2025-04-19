<?php

declare(strict_types=1);

namespace App\Service;

use App\Models\CustomWebhook;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

final class CustomWebhookService
{
    public function __construct(
        protected array $payload,
        protected array $headers,
    ) {}

    public function __invoke(): Response
    {
        CustomWebhook::query()->create(['payload' => $this->payload]);

        return new Response('Custom webhook received', HttpResponse::HTTP_OK);
    }
}
