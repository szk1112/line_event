<?php

namespace App\Providers;

use App\Events\LineBotAccountLinkEvent;
use App\Events\LineBotFollowEvent;
use App\Events\TextMessageEvent;
use App\Listeners\LineBotAccountLinkListener;
use App\Listeners\LineBotFollowListener;
use App\Listeners\TextMessageListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TextMessageEvent::class => [
            TextMessageListener::class,
        ],
        LineBotFollowEvent::class => [
            LineBotFollowListener::class,
        ],
        LineBotAccountLinkEvent::class => [
            LineBotAccountLinkListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
