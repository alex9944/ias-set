<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;



class ApiRegisterController extends Controller
{
    //

	public function create(Request $request)
	{
		//print_r($request);
		  try {
			     $check = DB::table('users')					
				->where('email','=',$request->email)
				->orWhere('phone_no','=',$request->phone_no)
				->first();				
					if($check)
					{
					$responsecode=201;		
					$header = array (
							'Content-Type' => 'application/json; charset=UTF-8',
							'charset' => 'utf-8'
						);  
				$this->data['msg'] =		'Emai id or Mobile number already created';							
				return response()->json($this->data, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
					}else
					{
							$user =  User::create([
							'first_name' => $request->first_name,									
							'email' => $request->email,
							'password' => bcrypt($request->password),
							'phone_no' => $request->phone_no,
							'token' => str_random(64),
							'activated' => !config('settings.activation')
						]);
								$role = Role::whereName('user')->first();
								$user->assignRole($role);
								
							
							$this->data['id']=$user->id;
							$this->data['email']=$user->email;
							$this->data['name']=$user->first_name;
							$this->data['image']=$user->image;
							$this->data['phone_no']=$user->phone_no;
							$this->data['msg'] =	'Success';
							
							$responsecode=201;								
							$header = array (
									'Content-Type' => 'application/json; charset=UTF-8',
									'charset' => 'utf-8'
								);   
						
						
						return response()->json($this->data, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
						
						
					}
						//$this->initiateEmailActivation($user);	
					
	
        } catch (Exception $e) {
			$responsecode=500;		
            return response()->json(['message' => $e->getMessage()], $responsecode);
        }
		
	}

	
}