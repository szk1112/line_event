<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use LINE\LINEBot\Event\FollowEvent;

class LineBotFollowEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public FollowEvent $events;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(FollowEvent $events)
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
