<?php


namespace App\Http\Controllers\V1;


use Illuminate\Http\Request;
use LINE\LINEBot;
use LINE\LINEBot\Event\MessageEvent\TextMessage;
use LINE\LINEBot\Event\PostbackEvent;
use Illuminate\Routing\Controller as BaseController;

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
                    $this->message($event);
                    break;
                case $event instanceof LINEBot\Event\FollowEvent:
                    // 友達登録＆ブロック解除
                case $event instanceof LINEBot\Event\MessageEvent\LocationMessage:
                    // 位置情報の受信
                case $event instanceof LINEBot\Event\UnfollowEvent:
                    // 友達解除
                    break;
                case $event instanceof LINEBot\Event\PostbackEvent:
                    // postback受信
                    $this->execute($event);
                    break;
                default:
                    $body = $event->getEventBody();
                    \Log::warning('Unknown event. [' . get_class($event) . ']', compact('body'));
            }
        }
    }

    /**
     * @param PostbackEvent $event
     *
     * @throws \ReflectionException
     */
    private function execute(PostbackEvent $event)
    {
        $replyToken   = $event->getReplyToken();
        $postbackData = $event->getPostbackData();
        parse_str($postbackData, $postbackArray);
        // postbackDataの中身を返却
        $this->bot->replyText($replyToken, print_r($postbackArray, true));

    }

    /**
     * @param TextMessage $event
     *
     * @throws \ReflectionException
     */
    private function message(TextMessage $event)
    {
        $replyToken = $event->getReplyToken();
        // オウム返し
        $text       = $event->getText();
        $this->bot->replyText($replyToken, $text);

    }

}
