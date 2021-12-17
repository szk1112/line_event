<?php


namespace App\Http\Controllers\V1;


use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use Illuminate\Routing\Controller as BaseController;
use LineBot\Usecase\LineBotAccountLinkAction;

class LineBotController extends BaseController
{

    private LINEBot $bot;

    public function __construct(LINEBot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * callback from LINE Message API(webhook)
     *
     * @param Request $request
     *
     * @throws \ReflectionException
     */
    public function callback(Request $request)
    {
        // Middlewareで処理済み
        $events = $request->get('events');
        // webhook通知(event)は場合があるため、ループ処理
        foreach ($events as $event) {
            switch (true) {
                case $event instanceof LINEBot\Event\MessageEvent\TextMessage:
                    // メッセージの受信
                    \App\Events\TextMessageEvent::dispatch($event);
                    break;
                case $event instanceof LINEBot\Event\FollowEvent:
                    \App\Events\LineBotFollowEvent::dispatch($event);
                    break;
                    // 友達登録＆ブロック解除
                case $event instanceof LINEBot\Event\AccountLinkEvent:
                    \App\Events\LineBotAccountLinkEvent::dispatch($event);
//                    $action = app(LineBotAccountLinkAction::class);
//                    $action->invoke($event);
                    break;
                case $event instanceof LINEBot\Event\MessageEvent\LocationMessage:
                    // 位置情報の受信
                case $event instanceof LINEBot\Event\UnfollowEvent:
                    // 友達解除
                case $event instanceof LINEBot\Event\PostbackEvent:
                    // スタンプ
                case $event instanceof LINEBot\Event\MessageEvent\StickerMessage:
                    // postback受信
                default:
                break;
            }
        }
    }

}
