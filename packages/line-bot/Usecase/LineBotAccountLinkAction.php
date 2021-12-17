<?php


namespace LineBot\Usecase;


use App\Models\LineUserNonce;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use LINE\LINEBot;
use LINE\LINEBot\Event\AccountLinkEvent;

class LineBotAccountLinkAction
{


    private LINEBot $bot;

    public function __construct(LINEBot $bot)
    {
        $this->bot = $bot;
    }

    /**
     *
     * @param AccountLinkEvent $event
     *
     * @throws \ReflectionException
     */
    public function invoke(AccountLinkEvent $event)
    {
        try{
            $lineUserNonce = LineUserNonce::query()
                                          ->where('nonce','=',$event->getNonce())
                                          ->first();
            $user = User::find($lineUserNonce->id);
            $user->line_user_id = $event->getUserId();
            $user->save();
            $lineUserNonce->delete();
            $this->bot->replyText($event->getReplyToken(),'連携しました！連携解除の導線確保は必須なのでしっかり案内すること');
        }catch(\Exception $e){
            $this->bot->replyText($event->getReplyToken(),'連携失敗しました！');
        }
    }

}
