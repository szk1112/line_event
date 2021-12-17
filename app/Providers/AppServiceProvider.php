<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use LINE\LINEBot;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \URL::forceScheme('https');
        $this->app->bind(LINEBot::class, function () {
            return new LINEBot(
                new LINEBot\HTTPClient\CurlHTTPClient(config('app.const.Line.LINE_BOT_CHANNEL_ACCESS_TOKEN')),
                ['channelSecret' => config('app.const.Line.LINE_BOT_CHANNEL_SECRET')]
            );
        });
    }
}
