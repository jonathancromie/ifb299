<?php
namespace BookShare\Http\Controllers\Auth;

use BookShare\Student;
use Validator;
use BookShare\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use View;
use Input;
use Auth;
use Illuminate\Http\Request;

use Socialite;
use Illuminate\Routing\Controller as SocialController;
use Request as SocialRequest; 

use Mail;

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

    protected $redirectPath = 'index';
    protected $loginPath = 'login';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Student $student)
    {
        // $this->middleware('guest', ['except' => 'getLogout']);
        $this->student = $student;
    }

    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('facebook')->user();

        // $id = $user->getId();
        $name = $user->getName();
        $email = $user->getEmail();
        $token = $user->token;
        // $avatar = $user->getAvatar();

        $credentials = [
            'email' => $email,
            'password' => $token
        ];

        $names = explode(' ', $name);

        

        // Auth::once($credentials)->redirect('auth.register')->with('names', $names);
        if (Auth::once($credentials)) {
            \Session::flash('message', 'Welcome '.$name.', we just need a few more details');
            return view('auth.register')->with('names', $names);
        }
        
        return redirect()->back()->withErrors('Login/Pass do not match')->withInput();



    }

    public function postLogin(Request $request) {

        //pass through validation rules
        $this->validate($request, ['email' => 'required', 'password' => 'required']);

        $credentials = [
            'email' => trim($request->get('email')),
            'password' => trim($request->get('password'))
        ];

        $remember = $request->has('remember');

        //log in the user
        if (Auth::attempt($credentials, $remember)) {
            \Session::flash('message', 'Welcome '.$request->get('email'));
            // return redirect()->intended($this->redirectPath);
            return view('index');
        }
        //show error if invalid data entered
        return redirect()->back()->withErrors('Login/Pass do not match')->withInput();

    }

    public function postRegister(Request $request)
    {
        $validator = $this->validator($request->all());

        if ($validator->fails()) {
            \Log::info($validator->errors()->all());
            $this->throwValidationException($request, $validator);
        }

        Auth::login($this->create($request->all()));

        $student = Auth::user();

        \Session::flash('message', 'You have successfully registered.');

        Mail::send('emails.welcome', ['student' => $student], function($message) use ($student){
            $message->from('sharebookqut@gmail.com', 'ShareBook');
            $message->to($student->email);
            $message->subject('Welcome to ShareBook');
        });

        

        return redirect('index');
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
            'email' => 'required|email|max:255|unique:students',
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'sex' => 'required|max:6',
            'dob' => 'required',
            'phone' => 'required|max:10',
            'street' => 'required|max:255',
            'suburb' => 'required|max:255',
            'post_code' => 'required|max:4',
            'state' => 'required|max:3',
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
        $student = Student::create([
            'email' => $data['email'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'sex' => $data['sex'],
            'dob' => $data['dob'],
            'phone' => $data['phone'],
            'street' => $data['street'],
            'suburb' => $data['suburb'],
            'post_code' => $data['post_code'],
            'state' => $data['state'],
            'password' => bcrypt($data['password']),
        ]);

        return $student;
    }
}