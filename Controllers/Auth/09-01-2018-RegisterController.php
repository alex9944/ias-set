<?php

namespace App\Http\Controllers\Auth;

use App\Traits\CaptchaTrait;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Traits\ActivationTrait;
use App\Models\User;
use App\Models\UserSubscription;
use App\Models\Role;
use App\Events\SignupVerified;
use App\Events\Registered;
use DB;
use Mail;
use Illuminate\Http\Request;
class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, ActivationTrait, CaptchaTrait;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/user';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('guest');

    }
public static function get_merchants_type()
	{			
			$mtype = DB::table('merchants_type')
				 ->orderby('title', 'asc')		 
				 ->get();
				$tit=''; 
				$tit .= '<select  name="merchant_type" class="form-control">';
				$tit .= '<option value="">Choose Artist Type</option>';				
				foreach($mtype as $mtype)
				 {	             
                    $tit .= '<option value="'.$mtype->id.'">'.$mtype->title.'</option>';
					}
				$tit .= '</select>';
			echo $tit;
		
	}
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

      //  $data['captcha'] = $this->captchaCheck();
                //'g-recaptcha-response'  => 'required',
               // 'captcha'               => 'required|min:1'
			   // 'g-recaptcha-response.required' => 'Captcha is required',
              //  'captcha.min'           => 'Wrong captcha, please try again.'
			  
			 
        $validator = Validator::make($data,
            [
                'first_name'            => 'required',                
                'email'                 => 'required|email|unique:users',
				'phone_no'                 => 'required|unique:users,phone_no',
                'password'              => 'required|min:6|max:20',
                'password_confirmation' => 'required|same:password',
				'user_type' => 'required',
				
               
            ],
            [
                'first_name.required'   => 'First Name is required',              
                'email.required'        => 'Email is required',
                'email.email'           => 'Email is invalid',
				'phone_no'                 => 'Mobile no is required',
                'password.required'     => 'Password is required',
                'password.min'          => 'Password needs to have at least 6 characters',
                'password.max'          => 'Password maximum length is 20 characters',
				'user_type'          => 'User type is required',
               
            ]
        );

        return $validator;

    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {	
    if($data['user_type']=='1')
		{
		$merchant_type='' ;
		}else{
			$merchant_type=$data['merchant_type'] ;
		}

        $user =  User::create([
            'first_name' => $data['first_name'],
			'user_type' => $data['user_type'],
			'user_type_sub' => $merchant_type,			
            'last_name' => '',
            'phone_no' => $data['phone_no'],
			'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'token' => str_random(64),
			'verification_code' => $this->random_code(),
            'activated' =>0 //!config('settings.activation')
        ]);
		
		if($data['user_type']=='2')
		{
		$role = Role::whereName('artist')->first();
        $user->assignRole($role);
	//	$this->redirectTo='/artist';
		}else{
			$role = Role::whereName('user')->first();
			$user->assignRole($role);
		//	$this->redirectTo='/user';
		}
      // $this->initiateEmailActivation($user);	
	  
	    session(['user_id' => $user->id]);
		$this->redirectTo='/verify';

		return $user;
		
      // return $user;		

    }
	 public function register(Request $request)
    {
		$this->validator($request->all())->validate();
		event(new Registered($user = $this->create($request->all())));
		//$this->guard()->login($user);

		return $this->registered($request, $user)
						?: redirect($this->redirectPath());

    }
	
	public function verify()
	{
		$user_id = session('user_id');
		
		if($user_id)
		{
		
			$user = User::find($user_id);
			
			if($user)
			{
				return view('auth.verify');
			}
		}
		
		return redirect('register');
	}
	public function resend()
	{
		$user_id = session('user_id');
		
		if($user_id)
		{
		
		$user = User::find($user_id);			
		$mail_content = array();
		$mail_content['email'] = $user->email;
		$mail_content['name'] = $user->first_name;
		$mail_content['verification_code'] = $user->verification_code;
		$mail_content['phone_no'] = $user->phone_no;		
		$mail_content['site_name'] = 'Indiameurals';
		
		//Send Mobile
		$sms_content='Your%20verification%20code%20is%20'.$mail_content['verification_code'].'%20This%2code%20is%20working%20on%2012%20hours';
		 $url="http://trans.smsfresh.co/api/sendmsg.php?user=malathy&pass=WiFiNIM@2017&sender=IMARTS&phone=".$mail_content['phone_no']."&text=".$sms_content."&priority=ndnd&stype=normal";
	
		$ch = curl_init();
		// Disable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		// Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		// Set the url
		curl_setopt($ch, CURLOPT_URL,$url);
		// Execute
		$result=curl_exec($ch);
		// Closing
		curl_close($ch);
								
	//	$dat=file_get_contents($url);
						
		// admin email
		/*$mail_content['admin_mail'] = 'malathy@indiameurals.com';
		Mail::send('emails.signup_verification', $mail_content, function($message)use ($mail_content) 
		{
			$email = $mail_content['email'];
			$message->to($email,'');
			
			if(isset($mail_content['admin_mail']))
				$message->bcc($mail_content['admin_mail'],'');
			
			$site_name = $mail_content['site_name'];
			$message->subject($site_name . ' | Your Signup Verification Code');

		});*/
		session(['user_id' => $user_id]);
				
				return redirect('/verify')->with('warning',"First please active your account.");
		}
		
		return redirect('register');
	}
	
	public function signup_verify(Request $request)
	{
		$user_id = session('user_id');
		
		if($user_id)
		{
		
			$user = User::find($user_id);
			
			if($user)
			{
			
				$rules = [			
					'verification_code'			=> 'required|is_valid'
				];		
				$messages = [
					'verification_code.required'		=> 'Verification code is required',						
					'verification_code.is_valid'		=> 'Invalid verification code'
				];
				
				Validator::extendImplicit('is_valid', function ($attribute, $value, $parameters, $validator) use($user) {
					return $value == $user->verification_code;
				});

				$this->validate($request, $rules, $messages);
				
				// update user status
				$user->activated = 1;
				if ($user->hasRole('merchant'))
					$user->merchant_status = 1;
				$user->save();
	   
				// signup verified event
				event(new SignupVerified($user));
				
				$this->guard()->login($user);
				
				return redirect($this->redirectPath());
			}
		}
		
		return redirect('register');
	}

}