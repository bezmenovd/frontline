<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property ?int $user_id
 * @property ?User $user
 * @property string $text
 * @property ?int $host_id
 * @property ?Host $host
 */
class ChatMessage extends Model
{
    protected $guarded = [
        'id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class, 'host_id', 'id');
    }
}
