<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
//use App\Transformers\Json;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use DB;
//use App\Transformers\Json;
class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
	
	public function getResetToken(Request $request)
    {
		//return $request->email;
		
        $this->validate($request, ['email' => 'required|email']);
      //  if ($request->wantsJson()) {
			
            $user = User::where('email', $request->email)->first();
			//dd(DB::getQueryLog($user));
            if (!$user) {
				
              //  return response()->json(Json::response(null, trans('passwords.user')), 400);
			  $responsecode = 200;        
		  $header = array (
					'Content-Type' => 'application/json; charset=UTF-8',
					'charset' => 'utf-8'
				);  
			$data['status']='Invalid email';
			return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $token = $this->broker()->createToken($user);
			$responsecode = 200;        
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );  
			$data['status']=$token;
			return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
          //  return response()->json(Json::response(['token' => $token]));
       // }
    }
}