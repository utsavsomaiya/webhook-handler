<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CustomWebhookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CustomWebhook extends Model
{
    /**
     * @use HasFactory<CustomWebhookFactory>
     */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
