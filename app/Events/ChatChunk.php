<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatChunk implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $streamId,
        public string $token,
        public bool $done = false,
    ) {}

    public function broadcastOn(): Channel
    {
        // canale pubblico per semplicità (si può passare a PrivateChannel se serve auth)
        return new Channel('chat.thread.' . $this->streamId);
    }

    public function broadcastAs(): string
    {
        return 'ChatChunk';
    }
}


