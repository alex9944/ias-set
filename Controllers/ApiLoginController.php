<?php

//namespace App\Http\Controllers;
namespace App\Http\Controllers;
use App\Http\Controllers\Auth;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class ApiLoginController extends Controller
{
    //
use AuthenticatesUsers;

    /**
     * Auth guard
     *
     * @var
     */
    protected $auth;
	
	public function login(Request $request)
	{
		$email      = $request->email;
        $password   = $request->password;
       // $remember   = 1;
		  try {
			$user=$this->auth->attempt([
            'email'     => $email,
            'password'  => $password
        ]);
						//$this->initiateEmailActivation($user);	
					$responsecode=201;		
					$header = array (
							'Content-Type' => 'application/json; charset=UTF-8',
							'charset' => 'utf-8'
						);       
				return response()->json($user, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	
        } catch (Exception $e) {
			$responsecode=500;		
            return response()->json(['message' => $e->getMessage()], $responsecode);
        }
		
	}

	
}