<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LINE\LINEBot\Event\MessageEvent\TextMessage;

class TextMessageEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TextMessage $events;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TextMessage $events)
    {
        $this->events = $events;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
