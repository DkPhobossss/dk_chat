<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatWithLastMessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_seen_message_id' => $this->pivot->last_seen_message_id,
            'last_message' => (isset($this->messages[0]) ? [
                'id' => $this->messages[0]->id,
                'body' => $this->messages[0]->body
            ]
                : null
            ),
        ];
    }
}
