<?php

namespace App\Models;

use App\Models\Host\Size;
use App\Models\Host\Water;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $finished_at
 * @property Collection<User> $users
 * @property Collection<ChatMessage> $chatMessages
 * @property Collection<GameResult> $results
 * @property int $players
 * @property Size $size
 * @property Water $water
 */
class Game extends Model
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

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'host_id', 'id');
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'host_id', 'id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(GameResult::class, 'game_id', 'id');
    }
}
