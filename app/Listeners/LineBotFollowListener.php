<?php

namespace App\Listeners;

use App\Events\LineBotFollowEvent;
use Exception;
//use LINE\LINEBot\Event\LineBotFollowEvent;
use LineBot\Usecase\LineBotFollowAction;

class LineBotFollowListener
{
    public LineBotFollowAction $usecase;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        LineBotFollowAction $usecase
    )
    {
        $this->usecase = $usecase;
    }

    /**
     * Handle the event.
     *
     * @param LineBotFollowEvent $event
     *
     * @return void
     */
    public function handle(LineBotFollowEvent $event)
    {
        try {
            $this->usecase->invoke($event->events);
        } catch (Exception $e) {
            \Log::info('Failed to reply message.');
        }

    }
}
