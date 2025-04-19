<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\StripeTransactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class StripeTransaction extends Model
{
    /**
     * @use HasFactory<StripeTransactionFactory>
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
            'meta' => 'array',
        ];
    }
}
