<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatMessage;
use App\Models\Host;
use App\Models\User;

class LobbyController
{
    public function fetchLobby()
    {
        usleep(500000);
        
        $chatMessages = ChatMessage::query()
            ->where('created_at', '>', now()->startOfDay())
            ->whereNull('host_id')
            ->whereNull('game_id')
            ->with('user')
            ->get();

        $hosts = Host::query()
            ->with('user', 'users')
            ->get();

        return response()->json([
            'chatMessages' => $chatMessages->map(fn(ChatMessage $cm) => [
                'id' => $cm->id,
                'datetime' => $cm->created_at->addHours(3)->format("H:i"),
                'user' => $cm->user ? [
                    'id' => $cm->user->id,
                    'name' => $cm->user->name,
                ] : [
                    'id' => 0,
                    'name' => '',
                ],
                'text' => $cm->text,
                'host_id' => $cm->host_id,
            ])->toArray(),
            'hosts' => $hosts->map(fn(Host $h) => [
                'id' => $h->id,
                'user' => [
                    'id' => $h->user->id,
                    'name' => $h->user->name,
                ],
                'description' => $h->description,
                'players' => $h->players,
                'size' => $h->size->value,
                'water' => $h->water->value,
                'users' => $h->users->map(fn(User $u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                ])->toArray(),
                'chatMessages' => $h->chatMessages->map(fn(ChatMessage $cm) => [
                    'id' => $cm->id,
                    'datetime' => $cm->created_at->addHours(3)->format("H:i"),
                    'user' => $cm->user ? [
                        'id' => $cm->user->id,
                        'name' => $cm->user->name,
                    ] : [
                        'id' => 0,
                        'name' => '',
                    ],
                    'text' => $cm->text,
                ])->toArray()
            ])->toArray(),
        ]);
    }
}
