<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use LINE\LINEBot;
use LINE\LINEBot\Constant\HTTPHeader;

class MargeRequestWithLINEBotEvent
{
    /** @var LINEBot */
    private LINEBot $lineBot;

    public function __construct(LINEBot $lineBot)
    {
        $this->lineBot = $lineBot;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     * @throws LINEBot\Exception\InvalidEventRequestException
     * @throws LINEBot\Exception\InvalidSignatureException
     */
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header(HTTPHeader::LINE_SIGNATURE);

        if(!$this->lineBot->validateSignature($request->getContent(), $signature)){
            abort(400);
        };
        // LINEのイベント構築
        $events = $this->lineBot->parseEventRequest(
            $request->getContent(),
            $signature
        );
        $request->merge(['events' => $events]);

        return $next($request);
    }

}
