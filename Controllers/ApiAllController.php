<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\SubscriptionPricing;
use App\Models\Country;
use App\Models\Currency;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionDuration;
use App\Models\SubscriptionFeatures;
use App\Models\MerchantsType;
use App\Models\Category;
use App\Models\UserSubscription;
use App\Models\Orders;
use App\Models\ArtiestProductSubscription;
use App\Models\User;
use App\Models\RefferArtist;
use App\Models\UserLibrary;
use App\Models\Role;
use App\Models\FreeResources;
use App\Models\Billingaddress;
use App\Models\UserOtp;
use App\Models\MoviesType;
use App\Models\Movies;
use App\Traits\ActivationTrait;
use App\Models\LovelywallSubscription;
use App\Models\LovelywallSubscriptionPricing;
use App\Models\UserSubscriptionApp;


use Image;
use Session;
use DB;
use App\Models\LovelywallSubscriptionFeatures;
use Mail;
class ApiAllController extends Controller
{
	    use RegistersUsers, ActivationTrait;
	public function movies()
    {	      
	   
	   $data['category']=MoviesType::with('movies')->get();
	  
			$responsecode = 200;        
             $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	
	 public function DynamicPages($slug = null)
    {

		 $data['page'] = DB::table('menu')
			->select('pages.title', 'pages.description')
			->leftJoin('pages', 'pages.menu_id', '=', 'menu.id')
			->where('menu.slug', '=', $slug)
			->first();
		if($data['page']){
			$responsecode = 200;        
             $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		
		}else{
			$data['status']='fail';
			$responsecode = 200;        
             $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		}
		
		

    }
	 public function freeimages()
    {	      
	   
	   $freeresources=FreeResources::all();
	   $data['stock-images']=$freeresources;
			$responsecode = 200;        
             $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	
	public function forgotUsername($mobile)
    {	
       $users = DB::table('users')
					 ->where('phone_no', $mobile)	 
					 ->first();
				if($users)
					 {
						$six_digit_random_number = mt_rand(100000, 999999);
						$UserOtp = new UserOtp;
						$UserOtp->user_id=$users->id;
						$UserOtp->otp=$six_digit_random_number;
						$UserOtp->status='1';
						$UserOtp->save();
						$id=$UserOtp->id;
						$sms_content='Your%20one%20time%20password%20is%20'.$six_digit_random_number.'%20This%20OTP%20is%20working%20on%2012%20hours';
						$url="http://trans.smsfresh.co/api/sendmsg.php?user=malathy&pass=WiFiNIM@2017&sender=IMARTS&phone=".$mobile."&text=".$sms_content."&priority=ndnd&stype=normal";
						$dat=file_get_contents($url);
						$val=explode('.',$dat);
						if($val[0]=='S')
						{
						 $data['status']='OTP sent in your mobile number successfully.';
						 $data['id']=$id;
						}else{
						 $data['status']='Mobile number is invalid.';
						}
					 }else{
						 $data['status']='Your mobile number is not in our records.';
					 }					 
			$responsecode = 200; 		
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	public function getforgotUsername($otp,$id)
    {	
       $otp_data = DB::table('user_otp')
					 ->where('otp', $otp)
					 ->where('id', $id)
					 ->where('status', 1)					 
					 ->first();
				if($otp_data)
					 {
						
						$users = DB::table('users')
						 ->where('id', $otp_data->user_id)	 
						 ->first();	
						if($users)
						 {					 
							$userotp=DB::table('user_otp')
							->where('id', $otp_data->id)
							->update(['status' => 2]);						
							 $data['status']='Your username is '.$users->email;
						 }else{
						 $data['status']='Invalid otp.';
						 }
						
					 }else{
						 $data['status']='Invalid OTP';
					 }					 
			$responsecode = 200; 		
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	
	public function get_user_library($id)
    {	
	/*
	->select('*', 'products.id as pid')				
			->leftJoin('category', 'category.c_id', '=', 'user_library.category_id')
			->leftJoin('products', 'products.id', '=', 'user_library.product_id')			
			->where('user_library.user_id','=',$id)
			*/
       $user_library = DB::table('user_library')			
						->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type') 						
						->leftJoin('category', 'category.c_id', '=', 'user_library.category_id')
						->leftJoin('products', 'products.id', '=', 'user_library.product_id')
						->leftJoin('users', 'users.id', '=', 'products.merchant_id')
						->leftJoin('merchants_type', 'merchants_type.id', '=', 'users.user_type_sub')
						->leftJoin('user_subscription', 'user_subscription.u_s_id', '=', 'user_library.u_s_id')
						->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
						->where('user_subscription.status','=','Active')			
						->where('user_library.user_id','=',$id)
						->get();
						
			$responsecode = 200; 		
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($user_library , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	
	public function api_dashboard($user_id) {
	$sub = DB::table('user_subscription')
					 ->where('status', 'Active')
					 ->where('user_id', $user_id)	 
					 ->first();	
//dd(DB::GetQueryLog());					 
					 $subcription_tot="";
					 $used_sub="";
					 $bal="";
					 $arrays='';
					 if($sub)
					 {
						 $u_s_id=$sub->u_s_id;
							 $sub_pricing = DB::table('subscription_pricing')					
							 ->where('id', $sub->package_type_id)	 
							 ->first();	
							 
							 $f_ids = explode(',',$sub_pricing->f_id);
							
							// print_r($f_ids);
							 $f_values = explode(',',$sub_pricing->f_value);						
							 $array=array_combine($f_ids,$f_values);
								$arrays = array();
								
							 foreach ($array as $key => $value)
							 {
								 $bal =self::art_type_balance($user_id,$key,$u_s_id);
								 $subs = DB::table('merchants_type')								
								 ->where('id', $key)	 
								 ->first();
								$balance=$value-$bal;								
								 $title=$subs->title;								
								$arrays[$title.'-'.$balance] = $value;							
							 }
							
							 
					 }	

					//$user_id= Auth::user()->id;		
	
	$sub_pricing = DB::table('user_subscription')
			->select('subscription_pricing.id as pid','subscription_plan.title as ptitle','subscription_pricing.f_value as f_value','subscription_pricing.f_id as f_id','subscription_duration.title as duration','subscription_duration.days_month as days_month_year')
			->leftJoin('subscription_pricing', 'subscription_pricing.id', '=', 'user_subscription.package_type_id')
			->leftJoin('subscription_duration', 'subscription_duration.id', '=', 'subscription_pricing.d_id')		
			->leftJoin('subscription_plan', 'subscription_plan.id', '=', 'subscription_pricing.p_id')	
			->where('user_subscription.status', '=', 'Active')			
			->where('user_subscription.user_id', '=', $user_id)			
			->first();	
			$responsecode = 200; 
$arrays['features'] = MerchantsType::all();			
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );  
	$arrays['current_package']=$sub_pricing;		
   return response()->json($arrays , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}


	public function art_type_balance($user_id,$key,$u_s_id)
    {
		  $users = DB::table('user_library')
							 ->where('user_id', $user_id)
							->where('art_type', $key)
							->where('u_s_id', $u_s_id)							
							 ->get()->count();
							// dd(DB::GetQueryLog());
							//$used_sub=count($users);
							//$bal=$value-
						return $users;
	}
	public static function add_user_library_other($val,$m_c_id,$user_id,$merchant_id,$art_type,$u_s_id) {
				$UserLibrary = new UserLibrary;
				$UserLibrary->product_id=$val;
				$UserLibrary->category_id=$m_c_id;
				$UserLibrary->art_type=$art_type;
				$UserLibrary->u_s_id=$u_s_id;				
				$UserLibrary->user_id=$user_id;			
				$UserLibrary->save();
			//	Artist
			$artiest = DB::table('users')
			->where('id',$merchant_id)
			->first();			
			$merchants_type = DB::table('merchants_type')
			->where('id',$artiest->user_type_sub)
			->first();	
			
			//Artist Product Subscription
										 
			$ArtiestProductSubscription = new ArtiestProductSubscription;
				$ArtiestProductSubscription->artlover_user_id=$user_id;
				$ArtiestProductSubscription->artiest_user_id=$merchant_id;
				$ArtiestProductSubscription->product_id=$val;
				$ArtiestProductSubscription->amount=$merchants_type->commission;
				$ArtiestProductSubscription->status="Unpaid";
				$ArtiestProductSubscription->save();
				
	 }
	public function add_my_library($id,$uid) {
		if(!empty($uid)){
					 $user_id=$uid;
					 
					 
																						 
												//check user login subscription pakage start				
														$sub = DB::table('user_subscription')
															 ->where('status', 'Active')
															 ->where('user_id', $user_id)	 
															 ->first();
													
															 if($sub)
															 {   
																$sub_pricing = DB::table('subscription_pricing')					
																->where('id', $sub->package_type_id)	 
																->first();
																$f_ids	=explode(',',$sub_pricing->f_id);
																$f_values =explode(',',$sub_pricing->f_value);
																$array=array_combine($f_ids,$f_values);
																//Cart add in My Library Start
																$success_data='';
																$already_data='';
																$limit_data='';
																//$ss='';
																		if($id)
																		{
																				$user_library = DB::table('user_library')
																				->where('product_id', $id)
																				->where('user_id',$user_id)
																				->where('u_s_id',$sub->u_s_id)																				
																				->first();
																				if(empty($user_library))
																				{
																					
																					//Check Art Type ex Proffesional,Promo...etc Start
																					foreach ($array as $key => $value)
																						{
																							
																							
																							$merchants_type = DB::table('merchants_type')																							
																							->where('id', $key)																							
																							->first();
																							$mt_title=$merchants_type->title;
																							
																							$type = DB::table('products')
																							->leftJoin('users', 'users.id', '=', 'products.merchant_id')
																							->where('products.id', $id)
																							->where('users.user_type_sub', $key)
																							->first();
																							
																							if($type)
																							{																
																								 $bal =self::art_type_balance($user_id,$type->user_type_sub,$sub->u_s_id);
																								//sleep(1);
																								//check art type balance start
																								if($value =='UNLIMITED')
																								{
																									//$ss.=$bal;
																										self::add_user_library_other($id,$type->m_c_id,$user_id,$type->merchant_id,$type->user_type_sub,$sub->u_s_id);
																										
																											$responsecode = 200;        
																											$header = array (
																											'Content-Type' => 'application/json; charset=UTF-8',
																											'charset' => 'utf-8'
																											);  
																											$data['status']='Successfully added in your Library';		
																											return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																										
																								}elseif($value > $bal)
																								{
																									//$ss.=$bal;
																										self::add_user_library_other($id,$type->m_c_id,$user_id,$type->merchant_id,$type->user_type_sub,$sub->u_s_id);
																										
																											$responsecode = 200;        
																											$header = array (
																											'Content-Type' => 'application/json; charset=UTF-8',
																											'charset' => 'utf-8'
																											);  
																											$data['status']='Successfully added in your Library';		
																											return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																										
																								}else{
																									//$limit_data.=$mt_title.' Art Type limit is full,';
																						$responsecode = 200;        
																						$header = array (
																						'Content-Type' => 'application/json; charset=UTF-8',
																						'charset' => 'utf-8'
																						);  
																						$data['status']=$mt_title.' Art Type limit is full,';		
																						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																								}
																								//check art type balance end																								

																							}
																						}
																						
																					//Check Art Type ex Proffesional,Promo...etc End
																						
																				}else{	
																					$products = DB::table('products')
																					->where('id', $id)	 
																					->first();														
																					// $already_data.=$products->name.' already added,';	
																					 
																						$responsecode = 200;        
																						$header = array (
																						'Content-Type' => 'application/json; charset=UTF-8',
																						'charset' => 'utf-8'
																						);  
																						$data['status']=$products->name.' already added,';			
																						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
											
																				}
																		}else{
																			$responsecode = 200;        
																						$header = array (
																						'Content-Type' => 'application/json; charset=UTF-8',
																						'charset' => 'utf-8'
																						);  
																						$data['status']="Product is empty";			
																						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																			
																		}
																		
																		//print_r($ss);
																	//	$data = $success_data.'<br />'.$already_data.'<br />'.$limit_data;
																	//	return redirect('/cart')->with('message', $data);
																//Cart add in My Library End
																 
															 }else{				  
																	$responsecode = 200;        
																	$header = array (
																	'Content-Type' => 'application/json; charset=UTF-8',
																	'charset' => 'utf-8'
																	);  
																	$data['status']='Please subscribe any one pakage to continue';			
																	return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
															 }
																 
																 
		}
			else{
											$responsecode = 200;        
											$header = array (
											'Content-Type' => 'application/json; charset=UTF-8',
											'charset' => 'utf-8'
											);  
											$data['status']='Please login to continue';			
											return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	
		
		}
	}
	
	public function subscription()
    {
	
			
			$data['pricing'] = DB::table('subscription_pricing')
			->select('subscription_pricing.id as pid','subscription_pricing.price as price','subscription_plan.title as ptitle','subscription_pricing.f_value as f_value','subscription_pricing.f_id as f_id','subscription_duration.title as duration','subscription_duration.days_month as days_month_year')
			->leftJoin('countries', 'countries.id', '=', 'subscription_pricing.c_id')
			->leftJoin('subscription_plan', 'subscription_plan.id', '=', 'subscription_pricing.p_id')
			->leftJoin('subscription_duration', 'subscription_duration.id', '=', 'subscription_pricing.d_id')
			->leftJoin('currency', 'currency.id', '=', 'subscription_pricing.cur_id')->where('subscription_pricing.subscription_flag','1')		
			->get();
			
			$data['features'] = MerchantsType::all();
						
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	public function lovelywall_subscription_plan(Request $request)
    {

				$plan=$request->plan;	
				
					$package=DB::table('lovelywall_subscription_pricing')
						->where('id', $plan)						
						 ->first();	
					 
			if($package)
			{
					$data=$package->price;		
			
			}else{
				$data="fail";
			}			
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	public function lovelywall_subscription_status(Request $request)
    {
			$user_id=$request->user_id;
			 $users=LovelywallSubscription::where('user_id',$user_id)->first();

		//	dd(DB::getQueryLog());
			if(!$users)
			{
					
					$user =  new LovelywallSubscription;
					$user->user_id = $user_id;
					$user->order_id = $request->order_id;					
					$user->amount = $request->amount;
					$user->tracking_id = $request->tracking_id;
					$user->bank_ref_no = $request->bank_ref_no;
					$user->order_status = $request->order_status;
					$user->failure_message = $request->failure_message;
					$user->payment_mode = $request->payment_mode;
					$user->card_name = $request->card_name;
					$user->currency = $request->currency;					
					$user->billingaddress = $request->billingaddress;
					$user->billingcity = $request->billingcity;
					$user->billingstate = $request->billingstate;
					$user->billingcountry = $request->billingcountry;
					$user->billingemail = $request->billingemail;
					$user->billingname = $request->billingname;
					$user->billingphone = $request->billingphone;
					$user->billingpostalcode = $request->billingpostalcode;				
					$user->save();
					
if($request->order_status=="Success"){	
	
			$user=User::where('id',$user_id)->first();			
			$lprice=LovelywallSubscriptionPricing::where('id',$user->lovelywall_package_id)->first();
			$f_id=explode(",",$lprice->f_id);
			$f_value=explode(",",$lprice->f_value);
		
			foreach ($f_id as $k => $v) {
				$life[$k]=$v. '_'. $f_value[$k];
			}
			$status='';
			$id='';
			foreach ($life as $k => $v) {
				$idval=explode('_',$v);
				$id=$idval['0'];
				$status=$idval['1'];
					$features=LovelywallSubscriptionFeatures::find($id);					
					if($features->title=='YUPP TV')
					{
						if($status=='Yes')
						{						
							$this->_activate($user_id,$features->title);
						}
					}
					elseif($features->title=='F TV'){
						if($status=='Yes')
						{
							$this->_activate($user_id,$features->title);
						}
						
					}
					elseif($features->title=='IM MEMBERSHIP'){
						if($status=='Silver')
						{
							$this->_activateindiameural($user_id,$features->title);	

						}
						
					}
				
			}
	$data="success";			
						
}
			}else{
				$data="fail";
			}			
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	public function lovelywall_login_after_mob($user_id)
    {
		
		
		
			$users=User::where('id',$user_id)->first();
			$plan_id=$users->lovelywall_package_id;	
			
			$this->data['YUPP TV'] = UserSubscriptionApp::where(['user_id'=> $user_id,'status'=> 'Active','package'=> 'YUPP TV'])->first();
			$this->data['F TV'] = UserSubscriptionApp::where(['user_id'=> $user_id,'status'=> 'Active','package'=> 'F TV'])->first();
			$this->data['IM MEMBERSHIP'] = UserSubscription::where(['user_id'=> $user_id,'status'=> 'Active'])->first();
			
		//	dd(DB::getQueryLog());
			if($plan_id)
			{
					$user=LovelywallSubscription::find($plan_id);
					$lprice=LovelywallSubscriptionPricing::where('id',$plan_id)->first();											
					$this->data['features']=LovelywallSubscriptionFeatures::get();	
					$this->data['plan']=$lprice->plan;
					$this->data['subscripion']=$lprice;																				
					$this->data['user']=$user;	
					$this->data['status']=1;					
														
							
			}else{
					$this->data['status']=0;
			}			
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($this->data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	
	public function lovelywall_subscription_status_mob(Request $request)
    {
		
			$user_id=$request->user_id;
			$plan_id=$request->plan_id;
			 $users=LovelywallSubscription::where('user_id',$user_id)->first();
          $this->data['status']='';
		//	dd(DB::getQueryLog());
			if($user_id)
			{
					
					$user =  new LovelywallSubscription;
					$user->user_id = $user_id;
					$user->order_id = $request->order_id;					
					$user->amount = $request->amount;
					$user->tracking_id = $request->tracking_id;
					$user->bank_ref_no = $request->bank_ref_no;
					$user->order_status = $request->order_status;
					$user->failure_message = $request->failure_message;
					$user->payment_mode = $request->payment_mode;
					$user->card_name = $request->card_name;
					$user->currency = $request->currency;					
					$user->billingaddress = $request->billingaddress;
					$user->billingcity = $request->billingcity;
					$user->billingstate = $request->billingstate;
					$user->billingcountry = $request->billingcountry;
					$user->billingemail = $request->billingemail;
					$user->billingname = $request->billingname;
					$user->billingphone = $request->billingphone;
					$user->billingpostalcode = $request->billingpostalcode;				
					$user->save();
					
								if($request->order_status=="Success"){	
									
														
										$user_upd=DB::table('users')
										->where('id', $user_id)
										->update(['lovelywall_package_id' => $plan_id]);					
									
											$userdat = User::find($user_id);
											$lprice=LovelywallSubscriptionPricing::where('id',$plan_id)->first();
											$f_id=explode(",",$lprice->f_id);
											$f_value=explode(",",$lprice->f_value);
										
											foreach ($f_id as $k => $v) {
												$life[$k]=$v. '_'. $f_value[$k];
											}
											$status='';
											$id='';
											foreach ($life as $k => $v) {
												$idval=explode('_',$v);
												$id=$idval['0'];
												$status=$idval['1'];
													$features=LovelywallSubscriptionFeatures::find($id);					
													if($features->title=='YUPP TV')
													{
														if($status=='Yes')
														{						
															$this->data['YUPP TV']=$this->_activate($user_id,$features->title);
														}
													}
													elseif($features->title=='F TV'){
														if($status=='Yes')
														{
															$this->data['F TV']=$this->_activate($user_id,$features->title);
														}
														
													}
													elseif($features->title=='IM MEMBERSHIP'){
														if($status=='Silver')
														{
															$this->data['IM MEMBERSHIP']=$this->_activateindiameural($user_id,$features->title);	

														}
														
													}
												
											}	
											$this->data['features']=LovelywallSubscriptionFeatures::get();	
											$this->data['plan']=$lprice->plan;
											$this->data['subscripion']=$lprice;																				
											$this->data['user']=$user;
											
									$this->data['status']='success';			
														
								}
			}else{
					$this->data['status']='fail';
			}			
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($this->data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	
	//App Package Activate
	private function _activate($user_id, $pkg)
	{
					//$Today=date('Y-m-d');
					$sub_date=Date('Y-m-d', strtotime("+ 12 months"));
					$subscription_end_date=$sub_date.' '.date("h:i:sa");					
						$usersub = new UserSubscriptionApp;				
						$usersub->user_id=$user_id;
						$usersub->package=$pkg;	
						$usersub->subscription_date=date('Y-m-d').' '.date("h:i:sa");
						$usersub->subscription_end_date=$subscription_end_date;										
						$usersub->status='Active';						
						$usersub->save();
						return $usersub;
	}
	
	//Indiameurals Package Activate
	
	private function _activateindiameural($user_id, $pkg)
	{
					
							
										
								$package_id=7;
								$sub_date=Date('Y-m-d', strtotime("+ 12 months"));
								$subscription_end_date=$sub_date.' '.date("h:i:sa");

								$usersub = new UserSubscription;				
								$usersub->user_id=$user_id;
								$usersub->amount=0;
								$usersub->package_type_id=$package_id;
								$usersub->subscription_date=date('Y-m-d').' '.date("h:i:sa");
								$usersub->subscription_end_date=$subscription_end_date;
								$usersub->hw_status='';
								$usersub->hw_buy_type='';
								$usersub->hw_amount='';						
								$usersub->status='Active';						
								$usersub->save();
								return $usersub;
								
								/*		//$data=$user->id;
								$package=DB::table('subscription_pricing')
								->where('id', $package_id)
								->first();


							$subscription_duration=DB::table('subscription_duration')
								->where('id', $package->d_id)
								->first();

								//dd(DB::getQueryLog());
										$days_month=$subscription_duration->days_month;
										if($days_month=='1'){
													//Subscription End Date Days Calculation
													$duration=$subscription_duration->title;
													$Today=date('Y-m-d');
													$sub_date=Date('Y-m-d', strtotime("+".$duration." days"));
													$subscription_end_date=$sub_date.' '.date("h:i:sa");


										}elseif($days_month=='3'){
													//Subscription End Date Days Calculation
													$duration=$subscription_duration->title;
													$duration_months=12*$duration;
													$Today=date('Y-m-d');
													$sub_date=Date('Y-m-d', strtotime("+".$duration_months." months"));
													$subscription_end_date=$sub_date.' '.date("h:i:sa");


										}else{
													//Subscription End Date Month Calculation
													$duration=$subscription_duration->title;	
													$date=date('Y-m-d');
													$date = strtotime(date("Y/m/d", strtotime($date)) . "+".$duration." months");
													$subscription_end_date = date("Y/m/d",$date).' '.date("h:i:sa");

										} */
	}
	
	
	public function lovelywall_subscription_added(Request $request)
    {
			$users=User::where('email',$request->email)->orWhere('phone_no',$request->phone)->first();
		//	dd(DB::getQueryLog());
			if(!$users)
			{
				
			
					$user =  User::create([
					'first_name' => $request->name,
					'user_type' => 1,
					'user_type_sub' => 0,			
					'last_name' => '',
					'email' => $request->email,
					'address' => $request->street,
					'city' => $request->city,
					'states' => $request->states,
					'country' => $request->country,
					'postcode' => $request->postcode,
					'phone_no' => $request->phone,
					'lovelywall_package_id' => $request->plan,						
					'password' => bcrypt($request->password),
					'token' => str_random(64),
					'verification_code' => $this->random_code(),
					'activated' => 0 //!config('settings.activation')
				]);
				$role = Role::whereName('user')->first();
					$user->assignRole($role);
				//	$this->initiateEmailActivation($user);
				//Send Verification Code		
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

										});*/
					$data=$user->id;
			}else{
			//	dd(DB::getQueryLog());
				$data="fail";
			}			
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	public function lovelywall_subscription()
    {
	
			
			$data['pricing'] = DB::table('lovelywall_subscription_pricing')
			->select('lovelywall_subscription_pricing.id as pid','lovelywall_subscription_pricing.price as price','lovelywall_subscription_plan.title as ptitle','lovelywall_subscription_pricing.f_value as f_value','lovelywall_subscription_pricing.f_id as f_id')
			->leftJoin('lovelywall_subscription_plan', 'lovelywall_subscription_plan.id', '=', 'lovelywall_subscription_pricing.p_id')			
			->orderBy('lovelywall_subscription_pricing.id', 'asc')
			->get();
			///dd(DB::getQueryLog());
			$data['features'] = LovelywallSubscriptionFeatures::all();
						
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	
	
	//User Library Start
	public function update_profile(Request $request) {
		$data['status']='';
		if(!empty($request->uid)){		
			$user=DB::table('users')
						->where('id', $request->uid)->first();
				if($user)
				{					
					$photo = $request->file('photo');
					/* if($photo){
							$imagename = time().'.'.$photo->getClientOriginalExtension();   
							$destinationPath = public_path('/uploads/thumbnail');
							$thumb_img = Image::make($photo->getRealPath())->resize(100, 100);
							$thumb_img->save($destinationPath.'/'.$imagename,80);                    
							$destinationPath = public_path('/uploads/original');
							$photo->move($destinationPath, $imagename);
								  DB::table('users')
								->where('id', $request->uid)
								->update(['image' => $imagename,]);
							//	$data['photo']=$imagename;
					 } */
					
					
								$user=DB::table('users')
								->where('id', $request->uid)
								->update(['first_name' => $request->name,'phone_no' => $request->phone]);	
								//dd(DB::getQueryLog());
								 $usub = DB::table('users')
								->select('id','first_name','phone_no','image')	
								->where('id', $request->uid)			
								->first();
								$data['users_profile']=$usub;
								
							 $responsecode = 200;        
							  $header = array (
										'Content-Type' => 'application/json; charset=UTF-8',
										'charset' => 'utf-8'
									);  
									$data['status'].="success";
								return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
				}else{
					$responsecode = 200;        
					  $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
							$data['status'].="Invalid User";
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
				}
		}else{
			
			$responsecode = 200;        
					  $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
							$data['status'].="User id empty";
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
			
		}
		
	}
	public function update_photo(Request $request)
	{
		$return = array('status' => 0, 'msg' => 'User Id is required.');
		
		$user_id = $request->user_id;
		
		if($user_id)
		{
			$user = User::find($user_id);
			
			if($user)
			{
				$rules = [
					'photo'			=> 'required|image|mimes:jpeg,png,jpg,gif|max:1024'
				];				
				$messages = [
					'photo.required'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
					'photo.image'           	=> 'Image should be a jpeg,png,gif',
				];
				
				$validator = \Validator::make($request->all(), $rules, $messages);
				
				 if ($validator->fails()) {
					$return['data'] = $validator->errors()->toArray();
					$return['msg'] = 'Validation Issue';
					return response()->json($return);
				}
				
				// validation ok				
				$photo = $request->file('photo');
				$imagename = time() . '_' . rand(10,100) . '_' . strtolower($photo->getClientOriginalName());
				$destinationPath = public_path('/uploads/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->resize(100, 100);
				$thumb_img->save($destinationPath.'/'.$imagename,80);                    
				$destinationPath = public_path('/uploads/original');
				$photo->move($destinationPath, $imagename);
				
				$user->image = $imagename;	
				$user->save();
			
				$return = array('status' => 1, 'data' => $user, 'msg' => 'Success');
				
				
			}
		}
		
		return response()->json($return);
	}
	
	
	public function update_photo_ios(Request $request)
	{
		$return = array('status' => 0, 'msg' => 'User Id is required.');
		
		$user_id = $request->user_id;
		
		if($user_id)
		{
			$user = User::find($user_id);
			
			if($user)
			{
				$rules = [
					'photo'			=> 'required'
				];				
				$messages = [
					'photo.required'			=> 'required',
				];
				
				$validator = \Validator::make($request->all(), $rules, $messages);
				
				 if ($validator->fails()) {
					$return['data'] = $validator->errors()->toArray();
					$return['msg'] = 'Validation Issue';
					return response()->json($return);
				}
				
							
					$destinationPath = public_path('/uploads/original/');
					/*$image_parts = explode(";base64,",$request->photo);
					$image_type_aux = explode("image/", $image_parts[0]);
					$image_type = $image_type_aux[1];
					$image_base64 = base64_decode($image_parts[1]);*/
					$image = $request->photo;
					$image_base64 = base64_decode($image);
					
					$file = uniqid() . '.png';
					$gfile=$destinationPath . $file;
					file_put_contents($gfile, $image_base64);
					$thumb_img = Image::make($destinationPath . $file)->crop(100, 100);
					$destinationThumPath = public_path('/uploads/thumbnail');
				    $thumb_img->save($destinationThumPath.'/'.$file,80); 
				
				$user->image = $file;	
				$user->save();
				 
			
				$return = array('status' => 1, 'data' => $user, 'msg' => 'Success');
				
			}
		}
		
		
		return response()->json($return);
	}
	
	
	
	public function update_password($uid,$password) {
		if(!empty($uid)){
						$user=DB::table('users')
						->where('id', $uid)
						->update(['password' => \Hash::make($password)]);	
						
						$responsecode = 200;        
					  $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						return response()->json($user , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
		}
		
	}
	public function fogot_password($email) {
		if(!empty($email)){
						$user=DB::table('users')
						->where('email', $email)->first();
						
						$responsecode = 200;        
					  $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						return response()->json($user , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
		}
		
	}
	public function featured_artists() {
		
			$users['featured_artists'] = DB::table('users')	
			->select('id','first_name','banner','description','email')
			->where('featured_artists', '1')		
			->get();
						
					  $responsecode = 200;        
					  $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						return response()->json($users , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
		
		
	}
	public function getUserSubscription($user_id) {	
	$user_subscription = DB::table('user_subscription')	
									->select('*','subscription_plan.title')		 
									->leftJoin('subscription_pricing','subscription_pricing.id','=','user_subscription.package_type_id')
									->leftJoin('subscription_plan','subscription_plan.id','=','subscription_pricing.p_id')
									->where('user_subscription.status','=','Active')		 
									->where('user_id','=',$user_id)			
									->first();
			if($user_subscription)
			{				
						$data['user_subscription']=$user_subscription;						
						$responsecode = 200;        
						$header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['status']='1';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
			}else{
				$data['user_subscription']='';
					$responsecode = 200;        
						$header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['status']='2';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
			}
	}
	public function deleted_userlibrary($id)
	{	 
	
		 DB::table('user_library')
		 ->where('product_id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $responsecode = 200;        
				$header = array (
				'Content-Type' => 'application/json; charset=UTF-8',
				'charset' => 'utf-8'
				);  
				$data['status']='Successfully remove it';			
			return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	
		
	
	}
	public function user_library_history($uid) {
						$user_library_history = DB::table('user_library')
						->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type')						
						->leftJoin('category', 'category.c_id', '=', 'user_library.category_id')						
						->leftJoin('products', 'products.id', '=', 'user_library.product_id')
						->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
						->leftJoin('users', 'users.id', '=', 'products.merchant_id')
						->leftJoin('merchants_type', 'merchants_type.id', '=', 'users.user_type_sub')
						->leftJoin('user_subscription', 'user_subscription.u_s_id', '=', 'user_library.u_s_id')
						->where('user_subscription.status','=','Inactive')			
						->where('user_library.user_id','=',$uid)
						->paginate(12);
						if($user_library_history)
								{
								$user['user_library_history']=$user_library_history;
								}else{
									$user['user_library_history']='';
								}
						 $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($user , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
	public function myLibrary($id)
    {	
	
       $data['library'] = DB::table('user_library')
	   ->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type') 						
						->leftJoin('category', 'category.c_id', '=', 'user_library.category_id')
						->leftJoin('products', 'products.id', '=', 'user_library.product_id')
						->leftJoin('users', 'users.id', '=', 'products.merchant_id')
						->leftJoin('merchants_type', 'merchants_type.id', '=', 'users.user_type_sub')
						->leftJoin('user_subscription', 'user_subscription.u_s_id', '=', 'user_library.u_s_id')
						->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
						->where('user_subscription.status','=','Active')			
						->where('user_library.user_id','=',$id)
			->get();
			$responsecode = 200;        
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );  
				if(!empty($data)){
					//$userlibrary='Your Library is Empty';
					return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
				}else{
					
					return response()->json('Your Library is Empty' , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
					
					}
   
    }
	public function addMyLibrary($id,$uid) {
		if(!empty($uid)){
					 $user_id=$uid;
					 
					 
																						 
												//check user login subscription pakage start				
														$sub = DB::table('user_subscription')
															 ->where('status', 'Active')
															 ->where('user_id', $user_id)	 
															 ->first();
													
															 if($sub)
															 {   
																$sub_pricing = DB::table('subscription_pricing')					
																->where('id', $sub->package_type_id)	 
																->first();
																$f_ids	=explode(',',$sub_pricing->f_id);
																$f_values =explode(',',$sub_pricing->f_value);
																$array=array_combine($f_ids,$f_values);
																//Cart add in My Library Start
																$success_data='';
																$already_data='';
																$limit_data='';
																//$ss='';
																		if($id)
																		{
																				$user_library = DB::table('user_library')
																				->where('product_id', $id)
																				->where('user_id',$user_id)
																				->where('u_s_id',$sub->u_s_id)																				
																				->first();
																				if(empty($user_library))
																				{
																					
																					//Check Art Type ex Proffesional,Promo...etc Start
																					foreach ($array as $key => $value)
																						{
																							
																							
																							$merchants_type = DB::table('merchants_type')																							
																							->where('id', $key)																							
																							->first();
																							$mt_title=$merchants_type->title;
																							
																							$type = DB::table('products')
																							->leftJoin('users', 'users.id', '=', 'products.merchant_id')
																							->where('products.id', $id)
																							->where('users.user_type_sub', $key)
																							->first();
																							
																							if($type)
																							{																
																								 $bal =self::art_type_balance($user_id,$type->user_type_sub,$sub->u_s_id);
																								//sleep(1);
																								//check art type balance start
																								if($value == "UNLIMITED")
																								{
																									
																									//$ss.=$bal;
																										self::add_user_library_other($id,$type->m_c_id,$user_id,$type->merchant_id,$type->user_type_sub,$sub->u_s_id);
																										$responsecode = 200;        
																											$header = array (
																											'Content-Type' => 'application/json; charset=UTF-8',
																											'charset' => 'utf-8'
																											);  
																											$data['status']='Successfully added in your Library';		
																											return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																								}elseif($value > $bal)
																								{
																									//$ss.=$bal;
																										self::add_user_library_other($id,$type->m_c_id,$user_id,$type->merchant_id,$type->user_type_sub,$sub->u_s_id);
																										
																											$responsecode = 200;        
																											$header = array (
																											'Content-Type' => 'application/json; charset=UTF-8',
																											'charset' => 'utf-8'
																											);  
																											$data['status']='Successfully added in your Library';		
																											return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																										
																								}else{
																									
																						$responsecode = 200;        
																						$header = array (
																						'Content-Type' => 'application/json; charset=UTF-8',
																						'charset' => 'utf-8'
																						);  
																						$data['status']=$mt_title.' Art Type limit is full,';		
																						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																								}
																								//check art type balance end																								

																							}
																						}
																						
																					//Check Art Type ex Proffesional,Promo...etc End
																						
																				}else{	
																					$products = DB::table('products')
																					->where('id', $id)	 
																					->first();														
																				
																					 
																						$responsecode = 200;        
																						$header = array (
																						'Content-Type' => 'application/json; charset=UTF-8',
																						'charset' => 'utf-8'
																						);  
																						$data['status']=$products->name.' already added,';			
																						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
											
																				}
																		}else{
																			$responsecode = 200;        
																						$header = array (
																						'Content-Type' => 'application/json; charset=UTF-8',
																						'charset' => 'utf-8'
																						);  
																						$data['status']="Product is empty";			
																						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
																			
																		}
																		
																
																//Cart add in My Library End
																 
															 }else{				  
																	$responsecode = 200;        
																	$header = array (
																	'Content-Type' => 'application/json; charset=UTF-8',
																	'charset' => 'utf-8'
																	);  
																	$data['status']='Please subscribe any one pakage to continue';			
																	return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
															 }
																 
																 
		}
			else{
											$responsecode = 200;        
											$header = array (
											'Content-Type' => 'application/json; charset=UTF-8',
											'charset' => 'utf-8'
											);  
											$data['status']='Please login to continue';			
											return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	
		
		}
	}
	
	//Add Subscription
	
	public function addSubscription(Request $request) {
		//$pid,$uid
		if(!empty($request->user_id))
		{ 
			if(!empty($request->package))
				{
				$user_id=$request->user_id;			   
				$package_id=$request->package;				
				
				$package=DB::table('subscription_pricing')
						->where('id', $package_id)
						 ->first();
						 
				$subscription_duration=DB::table('subscription_duration')
						->where('id', $package->d_id)
						 ->first();
				//dd(DB::getQueryLog());
				$days_month=$subscription_duration->days_month;
							if($days_month=='1'){
								//Subscription End Date Days Calculation
								$duration=$subscription_duration->title;
									$Today=date('Y-m-d');
									$sub_date=Date('Y-m-d', strtotime("+".$duration." days"));
									$subscription_end_date=$sub_date.' '.date("h:i:sa");
								
								
							}elseif($days_month=='3'){
								//Subscription End Date Days Calculation
								$duration=$subscription_duration->title;
								$duration_months=12*$duration;
									$Today=date('Y-m-d');
									$sub_date=Date('Y-m-d', strtotime("+".$duration_months." months"));
									$subscription_end_date=$sub_date.' '.date("h:i:sa");
								
								
							}else{
								//Subscription End Date Month Calculation
								$duration=$subscription_duration->title;	
								$date=date('Y-m-d');
								$date = strtotime(date("Y/m/d", strtotime($date)) . "+".$duration." months");
								$subscription_end_date = date("Y/m/d",$date).' '.date("h:i:sa");
								
							}
						
						
						
						$usersub = new UserSubscription;				
						$usersub->user_id=$user_id;
						$usersub->amount=$package->price;
						$usersub->package_type_id=$package_id;
						$usersub->subscription_date=date('Y-m-d').' '.date("h:i:sa");
						$usersub->subscription_end_date=$subscription_end_date;
						$usersub->hw_status=$request->hardware;
						$usersub->hw_buy_type=$request->hardware_type;
						$usersub->hw_amount=$request->amount;						
						$usersub->status='Inactive';						
						$usersub->save();						
					    $subscription_id=$usersub->id;
					//	dd(DB::getQueryLog());
						
						if($request->amount != 0){
							$amount=$package->price+$request->amount;							
						}else{
							$amount=$package->price;
							
						}
						//Billing Address
						if($request->address=="1")
						{
						$billadd = new Billingaddress;						
						$billadd->user_id=$user_id;				
						$billadd->name=$request->firstname;						
						$billadd->b_email=$request->email;
						$billadd->b_phone=$request->phone;
						$billadd->b_pincode=$request->psc;				
						$billadd->b_city=$request->city;
						$billadd->b_state=$request->state;
						$billadd->b_country=$request->country;
						$billadd->b_address_1=$request->street;				
						$billadd->save();
						$bill_address=$billadd->id;
						}else{
							 DB::table('billing_address')
								->where('id', $request->exiting)
								->update(['name' => $request->firstname,
								'name' => $request->firstname,
								'b_email' => $request->email,
								'b_phone' => $request->phone,
								'b_pincode' => $request->psc,
								'b_city' => $request->city,
								'b_state' => $request->state,
								'b_country' => $request->country,
								'b_address_1' => $request->street,]);
							$bill_address=$request->exiting;
						}
						
						$orders = new Orders;
						$orders->subscription_id=$subscription_id;
						$orders->user_id=$user_id;
						$orders->total=$amount;
						$orders->bill_address_id=$bill_address;						
						$orders->payment_type='EBS';
						$orders->save();
						$order_number=$orders->id;
						
						$name=$request->firstname;
						$email=$request->email;
						$phone=$request->phone;
						$city=$request->city;
						$postal_code=$request->psc;
						$address=$request->street;						
						
					if($amount > 0){						
						$responsecode = 200;        
					   $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['order_number']=$order_number;
						$data['usersubcription_id']=$subscription_id;
						$data['user_id']=$user_id;
						$data['package_id']=$package_id;
						$data['status']='4';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);					
					}else{
					$orders=DB::table('user_subscription')
										->where('u_s_id', $subscription_id)
										->update(['status' => 'Active']);										
					$responsecode = 200;        
					   $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
					//	$data['order_number']=$order_number;
					//	$data['usersubcription_id']=$subscription_id;
					//	$data['user_id']=$user_id;
						//$data['package_id']=$package_id;
				$data['status']='3';
						
				$user_subscription = DB::table('user_subscription')	
				->select('*','subscription_plan.title')		 
				->leftJoin('subscription_pricing','subscription_pricing.id','=','user_subscription.package_type_id')
				->leftJoin('subscription_plan','subscription_plan.id','=','subscription_pricing.p_id')
			    ->where('user_subscription.status','=','Active')			 
				->where('user_id','=',$user_id)			
				->first();
				//dd(DB::getQueryLog());
				if($user_subscription)
					{
						$data['user_subscription']=$user_subscription;
					}else{
						$data['user_subscription']='';
					}
						
						
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
						
					}
					
					
				}else{
					$responsecode = 200;        
					   $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['status']='2';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
				}
					
		
		}else{
			$responsecode = 200;        
					   $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['status']='1';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
			//return redirect('register');
			}
	}
	
	
	public function response(Request $request)
{
$MerchantRefNo=$request->order_number;
$PaymentId=$request->paymentid;
$ResponseMessageval=$request->message;
$TransactionIDval=$request->transactionid;
			
				if($MerchantRefNo){
					
					
										$orders=DB::table('orders')
										->where('id', $MerchantRefNo)
										->update(['payment_status' => $ResponseMessageval,'payment_transactionid' => $TransactionIDval,'paymentid' => $PaymentId]);						
									$orders_id=DB::table('orders')
										->where('id', $MerchantRefNo)
										->first();
										//echo $MerchantRefval;
										//dd(DB::getQueryLog());
									
									$sub_id=$orders_id->subscription_id;
									$user_id=$orders_id->user_id;
									if($ResponseMessageval=='Transaction Successful')
									{
									$user=DB::table('user_subscription')
										->where('user_id', $user_id)
										->update(['status' => 'Inactive']);	
										
									$orders=DB::table('user_subscription')
										->where('u_s_id', $sub_id)
										->update(['status' => 'Active']);
										
									$user_subscription = DB::table('user_subscription')	
									->select('*','subscription_plan.title')		 
									->leftJoin('subscription_pricing','subscription_pricing.id','=','user_subscription.package_type_id')
									->leftJoin('subscription_plan','subscription_plan.id','=','subscription_pricing.p_id')
									->where('user_subscription.status','=','Active')		 
									->where('user_id','=',$user_id)			
									->first();
									//dd(DB::getQueryLog());
									if($user_subscription)
										{
										$data['user_subscription']=$user_subscription;
										}else{
										$data['user_subscription']='';
										}
						$responsecode = 200;        
						$header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['status']='3';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
									}else{
						$responsecode = 200;        
						$header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['status']='2';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);
									}
					
				} 
					   $responsecode = 200;        
					   $header = array (
								'Content-Type' => 'application/json; charset=UTF-8',
								'charset' => 'utf-8'
							);  
						$data['status']='1';
						return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE |JSON_UNESCAPED_SLASHES);				



    }
	//User Library Ends
	public function billing_address($uid)
    {
    	
     		$billingaddress= DB::table('billing_address')	
			->where('user_id', $uid)		
            ->get();
			
			if($billingaddress)
						{
						$data['billing_address']=$billingaddress;
						}else{
						$data['billing_address']='';
						}
	//dd(DB::getQueryLog());

	 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    }
	//Products Start
	 public function products()
    {
    	
     		$products= DB::table('products')
			->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type')
			->leftJoin('category', 'category.c_id','products.m_c_id')
			->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
			->leftJoin('users', 'users.id','products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id','users.user_type_sub')
			->where('products.status', 'Enable')
			->where('users.activated', '1')			
            ->paginate(12);	
	//dd(DB::getQueryLog());
	if($products)
										{
										$data['products']=$products;
										}else{
										$data['products']='';
										}
	 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    }
	
	 public function images()
    {
    	
     		$products= DB::table('products')
			->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type')
			->leftJoin('category', 'category.c_id','products.m_c_id')
			->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
			->leftJoin('users', 'users.id','products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id','users.user_type_sub')
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->where('products.audio_url',NULL)
			->where('products.video_url',NULL)			
            ->paginate(12);
			if($products)
										{
										$data['products']=$products;
										}else{
										$data['products']='';
										}
	//dd(DB::getQueryLog());
	 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    }
	
	public function videos()
    {
    	
     		$products= DB::table('products')
			->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type')
			->leftJoin('category', 'category.c_id','products.m_c_id')
			->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
			->leftJoin('users', 'users.id','products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id','users.user_type_sub')
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->where('products.audio_url',NULL)
			->where('products.video_url','!=',NULL)			
            ->paginate(12);	
			if($products)
										{
										$data['products']=$products;
										}else{
										$data['products']='';
										}
	//dd(DB::getQueryLog());

	 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    }
	public function audios()
    {
    	
     		$products= DB::table('products')
			->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type')
			->leftJoin('category', 'category.c_id','products.m_c_id')
			->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
			->leftJoin('users', 'users.id','products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id','users.user_type_sub')
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->where('products.audio_url','!=',NULL)
			->where('products.video_url',NULL)				
            ->paginate(12);	
			if($products)
										{
										$data['products']=$products;
										}else{
										$data['products']='';
										}
	//dd(DB::getQueryLog());
	 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    }
	
	public function showProducts($id)
    {
	//$products= Products::all()->where('id',$id)->first();
	//$data['products'][]=$products;	
	/*
	  $data['products'] = DB::table('products')
			->select('id','merchant_id','products.m_c_id','products.name','products.description','products.image','products.audio_url','products.video_url','products.duration','category.c_title')
			->leftJoin('category', 'category.c_id', '=', 'products.m_c_id')
			->where('products.id', '=', $id)
			->get();
			*/
			$data['products']= DB::table('products')
			->select('category.c_title as main_cat_title','sub.c_title as sub_cat_title','products.id as pid','products.merchant_id','products.duration','products.name','products.description','products.image','products.audio_url','products.video_url','merchants_type.title as art_type','users.first_name as artist_name')
			->leftJoin('category', 'category.c_id','products.m_c_id')
			->leftJoin('category as sub', 'sub.c_id','products.s_c_id')
			->leftJoin('users', 'users.id','products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id','users.user_type_sub')
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->where('products.id', '=', $id)			
            ->get();
			
    $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
    return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	//Products End
	//Category Start
 public function category()
    {
      $category=Category::all()
	  ->where('parent_id','0');
	  $data['category']=$category;
		 foreach ($category as $category){

						array_push($data, $category->subcategories);
									foreach ($category->subcategories as $subcategory)
									{
										array_push($data, $subcategory->subcategories); 	
									}
		 } 
      $responsecode = 200;        
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }
	
	 public function showCategory($id)
    {
		 $category=Category::all()->where('c_id',$id)->first(); 
		 $data['category'][]=$category;		
		 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
				 );  
   return response()->json($data, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	
    }
	public function subCategory($id)
		{
			
			$subcategory =DB::table('category')->where('parent_id',$id)->get();
			//dd(DB::getQueryLog($duration));			
            $data['subcategory']=$subcategory;			
			$responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
    return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 	
		}
}