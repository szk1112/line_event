<?php


namespace LineBot\Usecase;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use LINE\LINEBot;
use LINE\LINEBot\Constant\Flex\ComponentAlign;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\Event\FollowEvent;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;

class LineBotFollowAction
{

    private const LINE_LINK_TOKEN_API_URI = 'https://api.line.me/v2/bot/user/%s/linkToken';

    private LINEBot $bot;

    public function __construct(LINEBot $bot)
    {
        $this->bot = $bot;
    }

    /**
     * @param FollowEvent $event
     *
     * @throws \ReflectionException
     */
    public function invoke(FollowEvent $event)
    {
        $replyToken = $event->getReplyToken();
        $linkToken = $this->fetchLinkToken($event);
        if(is_null($linkToken)){
            $this->bot->replyText($replyToken, 'トークン発行失敗');
            return;
        }
        $idLink = new UriTemplateActionBuilder(
            'ID連携をする',
            'https://lc.jp.ngrok.io/login?linkToken='.$linkToken
        );
        $button = new ButtonTemplateBuilder(null, '友達登録ありがとうございます！', '', [$idLink]);
        $buttonMessage = new TemplateMessageBuilder('ID連携してね', $button);

        $this->bot->replyMessage($replyToken, $buttonMessage);
    }

    private function fetchLinkToken(FollowEvent $event)
    {
        $response = $this->bot->createLinkToken($event->getUserId());
        if($response->isSucceeded()){
            $body = $response->getJSONDecodedBody();
            return $body['linkToken'];
        }else{
            return null;
        }

    }


}
