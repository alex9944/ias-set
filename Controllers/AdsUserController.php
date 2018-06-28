<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use App\Models\Role;
use App\Models\UserAccessModules;
use App\Traits\ActivationTrait;
use App\Http\Controllers\Controller;
use Image;
use DB;

class AdsUserController extends Controller {

    public function index()
    {		
        return view('panels.admin.ads_users.users');
    }
	//Admin Users Display
	 public function users()
    {		
	//Get Users Details
	    $users = DB::table('users')
		->select('*', 'users.id as uid')
		->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
		->where('role_user.role_id', '=', 4)
		->get();
        return view('panels.admin.ads_users.users', ['users' => $users]);
    }
	
	//Admin Users Display
	public function getusers(Request $request)
		{
			 $id=$request->id;  
			 $users = DB::table('users')
			 ->where('id', $id)	 
			 ->first();   
		 return '{"view_details": ' . json_encode($users) . '}';
	}
	
	
	
	public function added(Request $request)
	{
		 
			$this->validate($request,
			 [
                'firstname'            	=> 'required',
                'lastname'             	=> 'required',
                'email'                 => 'required|email|unique:users',
                'password'              => 'required|min:6|max:20',
                'cpassword' 			=> 'required|same:password',
               
            ],
            [
                'firstname.required'   	=> 'First Name is required',
                'lastname.required'    	=> 'Last Name is required',
                'email.required'        => 'Email is required',
                'email.email'           => 'Email is invalid',
                'password.required'     => 'Password is required',
                'password.min'          => 'Password needs to have at least 6 characters',
                'password.max'          => 'Password maximum length is 20 characters',
				'cpassword.required'    => 'Confirm password is required',
               
            ]);
			
			
			
			$photo = $request->file('photo');
			 if($photo){
				$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->resize(100, 100);
				$thumb_img->save($destinationPath.'/'.$imagename,80);                    
				$destinationPath = public_path('/uploads/original');
				$photo->move($destinationPath, $imagename);
			 }else{
				$imagename="";				 
			 }
				$user = new User;
				$user->first_name=$request->firstname;
				$user->last_name=$request->lastname;
				$user->email=$request->email;				
				$user->image=$imagename;
                $user->password=bcrypt($request->password);
                $user->token=str_random(64);
                $user->activated=!config('settings.activation');				
				$user->save();	
				
				$role = Role::whereName('Advertiser')->first();				
				$user->assignRole($role);				 
							
				//$this->initiateEmailActivation($user);

			return redirect('admin/ads/users')->with('message','Users added successfully');
	}
	public function updated(Request $request)
	{	 
	
	$this->validate($request,
			 [
                'firstname'            	=> 'required',
                'lastname'             	=> 'required',
                'email'                 => 'required|email',               
               
            ],
            [
                'firstname.required'   	=> 'First Name is required',
                'lastname.required'    	=> 'Last Name is required',
                'email.required'        => 'Email is required',
                'email.email'           => 'Email is invalid',              
               
            ]);
			
			$photo = $request->file('photo');
			 if($photo){
					$imagename = time().'.'.$photo->getClientOriginalExtension();   
					$destinationPath = public_path('/uploads/thumbnail');
					$thumb_img = Image::make($photo->getRealPath())->resize(100, 100);
					$thumb_img->save($destinationPath.'/'.$imagename,80);                    
					$destinationPath = public_path('/uploads/original');
					$photo->move($destinationPath, $imagename);
						 DB::table('users')
						->where('id', $request->id)
						->update(['image' => $imagename,]);
			 }
			
			if($request->password)
			{
				 DB::table('users')
					->where('id', $request->id)
					->update(['password' => bcrypt($request->password),]);				
			}
				DB::table('users')
				->where('id', $request->id)	
				->update(['first_name' => $request->firstname,'last_name' => $request->lastname,'email' => $request->email,]);

	return redirect('admin/ads/users')->with('message','User updated successfully');
	}


	public function deleted(Request $request)
	{	 
	$id=$request->id;  
		 $blogs = DB::table('users')
		 ->where('id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $status['deletedtatus']='User deleted successfully';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}
	public function enable(Request $request)
	{	 
	$id=$request->id;
	DB::table('users')
	->where('id', $request->id)
	->update(['activated' => 2,]);
	$status['deletedtatus']='User status updated successfully';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}
	public function disable(Request $request)
	{	 
	$id=$request->id;
	DB::table('users')
	->where('id', $request->id)
	->update(['activated' => 1,]);
	$status['deletedtatus']='User status updated successfully';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}

	public function destroy(Request $request)
		{
			$cn=count($request->selected_id);
			if($cn>0)
			{
			$data = $request->selected_id;			
				foreach($data as $id) {
				DB::table('users')->where('id', $id)->delete();			
				}			
			} 
	return redirect('admin/ads/users')->with('message','Seltected Users are deleted successfully');			

	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	protected function validator(array $data)
    {

     $data['captcha'] = $this->captchaCheck();
              'g-recaptcha-response'  => 'required',
                'captcha'               => 'required|min:1'
			   'g-recaptcha-response.required' => 'Captcha is required',
              'captcha.min'           => 'Wrong captcha, please try again.'
        $validator = Validator::make($data,
            [
                'first_name'            => 'required',
                'last_name'             => 'required',
                'email'                 => 'required|email|unique:users',
                'password'              => 'required|min:6|max:20',
                'password_confirmation' => 'required|same:password',
               
            ],
            [
                'first_name.required'   => 'First Name is required',
                'last_name.required'    => 'Last Name is required',
                'email.required'        => 'Email is required',
                'email.email'           => 'Email is invalid',
                'password.required'     => 'Password is required',
                'password.min'          => 'Password needs to have at least 6 characters',
                'password.max'          => 'Password maximum length is 20 characters',
               
            ]
        );

        return $validator;

    }
	 protected function create(array $data)
    {

        $user =  User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'token' => str_random(64),
            'activated' => !config('settings.activation')
        ]);

        $role = Role::whereName('user')->first();
        $user->assignRole($role);

        $this->initiateEmailActivation($user);

        return $user;

    }

	public function roles()
    {		
	//Get Users Details
	    $users = DB::table('users')
		->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
		->where('role_user.role_id', '=', 2)
		->get();
        return view('panels.admin.roles', ['users' => $users]);
    }*/
	
}