<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Http\Response;
use App\Models\User;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Mail;


class ApiRegisterController extends Controller
{
    //

	public function create(Request $request)
	{
		
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
							'address' => $request->street_name,
							'city' => $request->city,
						    'states' => $request->states,
						    'postcode' => $request->pincode,
							'password' => bcrypt($request->password),
							'phone_no' => $request->phone_no,
							'verification_code' => $this->random_code(),
							'token' => str_random(64),
							'activated' => 0 //!config('settings.activation')
						]);
								$role = Role::whereName('user')->first();
								$user->assignRole($role);
							//	print_r($user);
							
										//Send Verification Code		
										$mail_content = array();
										$mail_content['email'] = $user->email;
										$mail_content['name'] = $user->first_name;
										$mail_content['verification_code'] = $user->verification_code;
										$mail_content['phone_no'] = $user->phone_no;		
										$mail_content['site_name'] = 'Indiameurals';
										
										//Send Mobile
										$sms_content='Your%20verification%20code%20is%20'.$mail_content['verification_code'].'%20This%20code%20is%20working%20on%2012%20hours';
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
								
														
										// admin email
									/*		$mail_content['admin_mail'] = 'malathy@indiameurals.com';
									Mail::send('emails.signup_verification', $mail_content, function($message)use ($mail_content) 
										{
											$email = $mail_content['email'];
											$message->to($email,'');
											
											if(isset($mail_content['admin_mail']))
												$message->bcc($mail_content['admin_mail'],'');
											
											$site_name = $mail_content['site_name'];
											$message->subject($site_name . ' | Your Signup Verification Code');

										});*/
							
							$this->data['id']=$user->id;
							$this->data['email']=$user->email;
							$this->data['name']=$user->first_name;
							$this->data['street_name']=$user->address;
							$this->data['city']=$user->city;
							$this->data['states']=$user->states;
							$this->data['pincode']=$user->postcode;
							$this->data['image']=$user->image;
							$this->data['phone_no']=$user->phone_no;
							$this->data['activated'] =	$user->activated;
							$this->data['verification_code'] =	$user->verification_code;
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
	
	public function resend(Request $request)
	{
		
		$user = User::find($request->uid);
		if($user)
		{		
		$mail_content = array();
		$mail_content['email'] = $user->email;
		$mail_content['name'] = $user->first_name;
		$mail_content['verification_code'] = $user->verification_code;
		$mail_content['phone_no'] = $user->phone_no;		
		$mail_content['site_name'] = 'Indiameurals';
		
		//Send Mobile
		$sms_content='Your%20verification%20code%20is%20'.$mail_content['verification_code'].'%20This%20code%20is%20working%20on%2012%20hours';
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
								

						
		// admin email
	/*	$mail_content['admin_mail'] = 'malathy@indiameurals.com';
		Mail::send('emails.signup_verification', $mail_content, function($message)use ($mail_content) 
		{
			$email = $mail_content['email'];
			$message->to($email,'');
			
			if(isset($mail_content['admin_mail']))
				$message->bcc($mail_content['admin_mail'],'');
			
			$site_name = $mail_content['site_name'];
			$message->subject($site_name . ' | Your Signup Verification Code');

		});
		*/
		$users['verification_code']=$mail_content['verification_code'];
		$users['status']=1;
		 $responsecode=200;		
					$header = array (
							'Content-Type' => 'application/json; charset=UTF-8',
							'charset' => 'utf-8'
						);     
					
				return response()->json($users, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}else{
			$users['status']=2;
			 $responsecode=200;		
					$header = array (
							'Content-Type' => 'application/json; charset=UTF-8',
							'charset' => 'utf-8'
						);     
					
				return response()->json($users, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
		
	}
	
	public function verify(Request $request)
	{
		
		
			$user = User::find($request->uid);
			$vcode=$request->vcode;
			if($user)
			{
				if($user->verification_code==$vcode)
				{
					$userotp=DB::table('users')
							->where('id', $request->uid)
							->update(['activated' => 1]);
							
					$users['status']=1;
					$responsecode=200;		
					$header = array (
							'Content-Type' => 'application/json; charset=UTF-8',
							'charset' => 'utf-8'
						);     
					
				return response()->json($users, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				}else{
					$users['status']=2;
					$responsecode=200;		
					$header = array (
							'Content-Type' => 'application/json; charset=UTF-8',
							'charset' => 'utf-8'
						);     
					
				return response()->json($users, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				}
				
			}
		
	}

	
}