<?php

declare(strict_types=1);

use App\Http\Controllers\WebhookHandlerController;
use Illuminate\Support\Facades\Route;

Route::post('webhook', WebhookHandlerController::class)->name('webhook.handler');
