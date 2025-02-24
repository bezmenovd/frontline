<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $token
 * @property int $rating
 * @property int $host_id
 * @property ?Host $host
 * @property int $game_id
 * @property ?Game $game
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class, 'host_id', 'id');
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'id');
    }
}
