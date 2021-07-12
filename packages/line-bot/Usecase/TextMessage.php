<?php


namespace LineBot\Usecase;


use LINE\LINEBot;

class TextMessage
{

    private LINEBot $bot;

    public function __construct(LINEBot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @param LINEBot\Event\MessageEvent\TextMessage $event
     *
     * @throws \ReflectionException
     */
    public function invoke(LINEBot\Event\MessageEvent\TextMessage $event)
    {
        $replyToken = $event->getReplyToken();
        // オウム返し
        $text       = $event->getText();
        $this->bot->replyText($replyToken, $text);
    }
}
