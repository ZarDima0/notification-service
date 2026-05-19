<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'recipient_id' => $this->recipient_id,
            'status' => $this->status,
            'channel' => $this->channel,
            'message' => $this->message,
            'priority' => $this->priority,
            'sent_at' => $this->sent_at,
            'delivered_at' => $this->delivered_at,
        ];
    }
}
