<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GithubCommitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class GithubCommit extends Model
{
    /**
     * @use HasFactory<GithubCommitFactory>
     */
    use HasFactory;
}
