<?php

namespace App\Models;

use App\Models\Host\Size;
use App\Models\Host\Water;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property int $user_id
 * @property User $user
 * @property Collection<User> $users
 * @property Collection<ChatMessage> $chatMessages
 * @property string $description
 * @property int $players
 * @property bool $active
 * @property Size $size
 * @property Water $water
 */
class Host extends Model
{
    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'size' => Size::class,
            'water' => Water::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'host_id', 'id');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'host_id', 'id');
    }
}
