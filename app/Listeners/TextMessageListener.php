<?php

namespace App\Listeners;

use App\Events\TextMessageEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use LineBot\Usecase\TextMessage;

class TextMessageListener
{
    public TextMessage $usecase;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        TextMessage $usecase
    )
    {
        $this->usecase = $usecase;
    }

    /**
     * Handle the event.
     *
     * @param  TextMessageEvent  $event
     * @return void
     */
    public function handle(TextMessageEvent $event)
    {
        try {
            $this->usecase->invoke($event->events);
        } catch (Exception $e) {
            \Log::info('Failed to reply message.');
        }

    }
}
