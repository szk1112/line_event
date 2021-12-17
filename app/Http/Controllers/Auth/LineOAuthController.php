<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use InvalidArgumentException;
use LINE\LINEBot;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;

class LineOAuthController extends Controller
{
    private const LINE_OAUTH_URI       = 'https://access.line.me/oauth2/v2.1/authorize';
    private const LINE_TOKEN_API_URI   = 'https://api.line.me/oauth2/v2.1/';
    private const LINE_PROFILE_API_URI = 'https://api.line.me/v2/';

    private const USE_PKCE  = true;
    private const USE_STATE = true;

    private string $clientId;
    private string $clientSecret;
    private string $callbackUrl;

    private LINEBot $bot;

    public function __construct(LINEBot $bot)
    {
        $this->bot = $bot;

        $this->clientId     = config('line.client_id');
        $this->clientSecret = config('line.client_secret');
        $this->callbackUrl  = config('line.callback_url');
    }

    /**
     * LINE ログインフロー1
     *
     * @param Request $request
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirectToProvider(Request $request)
    {
        $state = null;
        if (self::USE_STATE) {
            request()->session()->put('state', $state = $this->getState());
        }
        if (self::USE_PKCE) {
            request()->session()->put('code_verifier', $this->getCodeVerifier());
        }
        return redirect($this->getAuthUrl($state));
    }

    /**
     * @param null $state
     *
     * @return string
     */
    private function getAuthUrl($state = null)
    {
        return self::LINE_OAUTH_URI . '?' . http_build_query($this->getCodeFields($state), '', '&');
    }

    /**
     * @param null $state
     *
     * @return array
     */
    private function getCodeFields($state = null)
    {
        $fields = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->callbackUrl,
            'scope'         => 'profile openid',
            'bot_prompt'    => 'aggressive',
        ];
        if (self::USE_STATE) {
            $fields['state'] = $state;
        }
        if (self::USE_PKCE) {
            $fields['code_challenge']        = $this->getCodeChallenge();
            $fields['code_challenge_method'] = $this->getCodeChallengeMethod();
        }
        return $fields;
    }

    /**
     *
     * LINE ログインフロー 2
     * handleProviderCallback
     *
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        if ($this->hasInvalidState()) {
            throw new InvalidArgumentException;
        }
        $code      = $request->query('code');
        $tokenInfo = $this->fetchTokenInfo($code);
        $userInfo  = $this->fetchUserInfo($tokenInfo->access_token);
        //  ログイン処理
        ///
        $response = $this->bot->pushMessage($userInfo->userId, new TextMessageBuilder('ID連携完了しました！'));
        if (!$response->isSucceeded()) {
            ///
        }
        $viewParam = [
            'displayName' => $userInfo->displayName ?? '',
            'userId'      => $userInfo->userId ?? '',
            'pictureUrl'  => $userInfo->pictureUrl ?? '',
        ];

        return redirect()->route('line.connect')->with($viewParam);
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
        try {
            $tokenInfo = $this->sendRequest(
                $base_uri,
                $method,
                $path,
                $headers,
                ['form_params' => $this->getTokenFields($code)]
            );
        } catch (GuzzleException $e) {
            return redirect()->home()->withErrors(['auth.error' => '認証失敗']);
        }
        return $tokenInfo;
    }

    protected function getTokenFields($code)
    {
        $fields = [
            'code'          => $code,
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri'  => $this->callbackUrl,
            'grant_type'    => 'authorization_code'
        ];

        if (self::USE_PKCE) {
            $fields['code_verifier'] = request()->session()->pull('code_verifier');
        }

        return $fields;
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

    /**
     * Generates a random string of the right length for the PKCE code verifier.
     *
     * @return string
     */
    protected function getCodeVerifier()
    {
        return Str::random(96);
    }

    /**
     * Generates the PKCE code challenge based on the PKCE code verifier in the session.
     *
     * @return string
     */
    protected function getCodeChallenge()
    {
        $hashed = hash('sha256', request()->session()->get('code_verifier'), true);

        return rtrim(strtr(base64_encode($hashed), '+/', '-_'), '=');
    }

    /**
     * Returns the hash method used to calculate the PKCE code challenge.
     *
     * @return string
     */
    protected function getCodeChallengeMethod()
    {
        return 'S256';
    }

    /**
     * Get the string used for session state.
     *
     * @return string
     */
    protected function getState()
    {
        return Str::random(40);
    }

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        if (!self::USE_STATE) {
            return false;
        }

        $state = request()->session()->pull('state');

        return empty($state) || request()->query('state') !== $state;
    }

}
