<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LineUserNonce;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @param Request $request
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm(Request $request): \Illuminate\View\View
    {
        $linkToken = $request->get('linkToken');
        return view('auth.login')->with(
            'linkToken',
            $linkToken
        );
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
//        $bag = new MessageBag();
//        $bag->add('already_link','既に連携済みです。解除してからもう一度連携をしてください。');
//        return redirect(route('login'))->withErrors($bag);
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            if ($request->hasSession()) {
                $request->session()->put('auth.password_confirmed_at', time());
            }
            $linkToken = $request->get('linkToken');
            if(!empty($linkToken)){

                $user     = Auth::user();
                if(!empty($user->line_user_id)){
                    Auth::logout();
                    return redirect()->route('login')->withErrors(['error'=>'既に連携済みです。解除してからもう一度連携をしてください。']);
                }
                $nonce    = Str::random();
                $lineUser = LineUserNonce::find($user->getAuthIdentifier());
                if (empty($lineUser)) {
                    $lineUser = LineUserNonce::create([
                                              'id'    => $user->getAuthIdentifier(),
                                              'nonce' => $nonce
                                          ]
                    );
                }else{
                    $lineUser->nonce = $nonce;
                }
                $lineUser->save();
                $redirectUrl = sprintf('https://access.line.me/dialog/bot/accountLink?linkToken=%s&nonce=%s',$linkToken,$nonce);
                return redirect()->away($redirectUrl);
            }

            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $request->validate([
                               $this->username() => 'required|string',
                               'password' => 'required|string',
                               'linkToken' => 'string|nullable',
                           ]);
    }

}
