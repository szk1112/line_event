<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineOAuthController extends Controller
{
    private const LINE_OAUTH_URI       = 'https://access.line.me/oauth2/v2.1/authorize?';
    private const LINE_TOKEN_API_URI   = 'https://api.line.me/oauth2/v2.1/';
    private const LINE_PROFILE_API_URI = 'https://api.line.me/v2/';

    private string $clientId;
    private string $clientSecret;
    private string $callbackUrl;

    private LINEBot $bot;

    public function __construct(LINEBot $bot)
    {
        $this->bot          = $bot;

        $this->clientId     = config('line.client_id');
        $this->clientSecret = config('line.client_secret');
        $this->callbackUrl  = config('line.callback_url');
    }

    public function redirectToProvider()
    {
        $csrfToken = Str::random(32);
        $queryData = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->callbackUrl,
            'state'         => $csrfToken,
            'scope'         => 'profile openid',
            'bot_prompt'    => 'normal',
        ];
        $queryStr  = http_build_query($queryData, '', '&');
        return redirect(self::LINE_OAUTH_URI . $queryStr);
    }

    public function handleProviderCallback(Request $request)
    {
        $code      = $request->query('code');
        $tokenInfo = $this->fetchTokenInfo($code);
        $userInfo  = $this->fetchUserInfo($tokenInfo->access_token);
        //  ログイン処理
        ///

        $response = $this->bot->pushMessage($userInfo->userId, new TextMessageBuilder('ログイン通知'));
        if(!$response->isSucceeded()){
            ///
        }
        return redirect()->route('user.home')->with(['displayName'=>$userInfo->displayName]);
    }

    private function fetchUserInfo($access_token)
    {
        $base_uri = ['base_uri' => self::LINE_PROFILE_API_URI];
        $method   = 'GET';
        $path     = 'profile';
        $headers  = [
            'headers' =>
                [
                    'Authorization' => 'Bearer ' . $access_token
                ]
        ];
        try {
            $userInfo = $this->sendRequest($base_uri, $method, $path, $headers);
        } catch (GuzzleException $e) {
            return redirect()->home()->withErrors(['auth.error'=>'認証失敗']);
        }
        return $userInfo;
    }

    private function fetchTokenInfo($code)
    {
        $base_uri    = ['base_uri' => self::LINE_TOKEN_API_URI];
        $method      = 'POST';
        $path        = 'token';
        $headers     = [
            'headers' =>
                [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
        ];
        $form_params = [
            'form_params' =>
                [
                    'code'          => $code,
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'redirect_uri'  => $this->callbackUrl,
                    'grant_type'    => 'authorization_code'
                ]
        ];
        try {
            $token_info = $this->sendRequest($base_uri, $method, $path, $headers, $form_params);
        } catch (GuzzleException $e) {
            return redirect()->home()->withErrors(['auth.error'=>'認証失敗']);
        }
        return $token_info;
    }

    /**
     * @param      $baseUri
     * @param      $method
     * @param      $path
     * @param      $headers
     * @param null $formParams
     *
     * @return mixed
     * @throws GuzzleException
     */
    private function sendRequest($baseUri, $method, $path, $headers, $formParams = null)
    {
        try {
            $client = new Client($baseUri);
            if ($formParams) {
                $option   = array_replace($headers, $formParams);
                $response = $client->request($method, $path, $option);
            } else {
                $response = $client->request($method, $path, $headers);
            }
        } catch (GuzzleException $e) {
            throw $e;
        }
        return json_decode($response->getbody()->getcontents());
    }
}
