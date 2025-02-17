<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatMessage;

class LobbyController
{
    public function fetchLobby()
    {
        $chatMessages = ChatMessage::query()
            ->where('created_at', '>', now()->startOfDay())
            ->with('user')
            ->get();

        return response()->json([
            'chat_messages' => $chatMessages->map(fn(ChatMessage $cm) => [
                'id' => $cm->id,
                'datetime' => $cm->created_at->addHours(3)->format("H:i"),
                'user' => [
                    'id' => $cm->user->id,
                    'name' => $cm->user->name,
                ],
                'text' => $cm->text,
                'reply_to' => $cm->reply_to,
            ]),
        ]);
    }
}
