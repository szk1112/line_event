<?php


namespace App\Listeners;


use App\Events\LineBotAccountLinkEvent;
use Exception;
use LineBot\Usecase\LineBotAccountLinkAction;

class LineBotAccountLinkListener
{
    public LineBotAccountLinkAction $usecase;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(
        LineBotAccountLinkAction $usecase
    )
    {
        $this->usecase = $usecase;
    }

    /**
     * Handle the event.
     *
     * @param LineBotAccountLinkEvent $event
     *
     * @return void
     */
    public function handle(LineBotAccountLinkEvent $event)
    {
        try {

            $this->usecase->invoke($event->events);


        } catch (Exception $e) {
            \Log::info('Failed to reply message.');
        }

    }
}
