<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

//use App\Customer;
//use App\Order;
//use App\Product;
//use App\Order_product;
//use App\Mail\NewOrder;
//use Mail;
use App\Models\Orders;
use App\Models\OrderProduct;
use App\Models\Products;
use App\Models\UserLibrary;
use App\Models\ArtiestProductSubscription;
use DB;
use Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
class CheckoutController extends Controller
{
	 public function index() {
		  //check user login cart is empty start
	$cart=session()->get('cart');
	$cart_tot=count($cart);
		if($cart_tot > 0)
			{
										 //check user login start
										 if(!empty(Auth::user()->id)){
														$user_id=Auth::user()->id;											 
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
																$success='';																
																$already_data='';
																$limit_data='';
																$artist_exists_ids = [];
																//$ss='';
																		foreach($cart as $val => $arr)
																		{
																				$user_library = DB::table('user_library')
																				->where('product_id', $val)
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
																							->where('products.id', $val)
																							->where('users.user_type_sub', $key)
																							->first();
																							
																							if($type)
																							{																
																								 $bal =self::art_type_balance($user_id,$type->user_type_sub,$sub->u_s_id);
																								//sleep(1);
																								//check art type balance start
																								if($value > $bal)
																								{
																									//$ss.=$bal;
																										self::add_user_library_other($val,$type->m_c_id,$user_id,$type->merchant_id,$type->user_type_sub,$sub->u_s_id);
																										$success=1;	
																										$cartData = session()->get('cart');

																												if (array_key_exists($val, $cartData)) {
																													unset($cartData[$val]);
																												}
																												session()->put('cart', $cartData);
																												$cartTotal = 0;
																												foreach ($cartData as $cartItem) {
																													$cartTotal = $cartTotal+$cartItem['qty'];
																												}

																										session()->put('total', $cartTotal);
																								}else{
																									if(!in_array($key,$artist_exists_ids)) {
																										$limit_data.= $mt_title.' Art Type limit is full, ';
																										$artist_exists_ids[] = $key;
																									}
																								}
																								//check art type balance end																								

																							}
																						}
																						
																					//Check Art Type ex Proffesional,Promo...etc End
																						
																				}else{	
																					$products = DB::table('products')
																					->where('id', $val)	 
																					->first();														
																					 $already_data.=$products->name.' already added,';														 
																				}
																		}
																		if($success==1)
																		{
																		$success_data="Successfully added in your Library";	
																		}else{
																		$success_data='';	
																		}
																		//print_r($ss);
																		$data = $success_data.'<br />'.$already_data.'<br />'.$limit_data;
																		return redirect('/cart')->with('message', $data);
																//Cart add in My Library End
																 
															 }else{				  
																 return redirect('/cart')->with('message', 'Please subscribe any one package to continue');
															 }
																 
												//check user login subscription pakage end					
										 }else{				
										return redirect('login');		
										}
										//check user login end
		}else{										
			return redirect('/cart')->with('message', 'Your cart is empty');									
		}
			  //check user login cart is empty start
		 
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
	/*
    public function index_1() {
		//Check User Login Start
		if(!empty(Auth::user()->id)){
			
			$user_id=Auth::user()->id;
			//Check User Subscription PAckage Start			
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
   foreach ($array as $key => $value)
   {
  
	 try { 
        
			$bal =self::count_category($user_id,$value,$key);
		
								$cart=session()->get('cart');
								$cart_tot=count($cart);
								if($bal >= $value)
									{
										
									$data=self::addlibrary($user_id,$cart,$bal,$value,$key);
									
									return redirect('/cart')->with('message', $data);
									}else{										
										return redirect('/cart')->with('message', 'Your subscription image limit is full please upgrade your subscription and then add it');
																	
									}
			

    } catch (Exception $e) { 
        echo 'Caught Exception: ',  $e->getMessage(), "\n"; 
    } 
		
		
    }
				 
					
						
					  }else{
						
					  
						 return redirect('/cart')->with('message', 'Please subscribe any one pakage to continue');
					 }
			//Check User Subscription PAckage End			
			}
			else{				
		return redirect('login');		
		}
		//Check User Login End
		
	} */
	
	// At A Time User Subsctiption Total Used 
	
	 public static function user_limit_total($user_id,$subcription_tot) {
						$users = DB::table('user_library')
						->where('user_id', $user_id)	 
						->get();
						$used_sub=count($users);
			return $bal=$subcription_tot-$used_sub;
	 }
	 
	 // User limit total category wise
	 public static function user_limit_total_category($user_id,$subcription_tot,$cid) {
							$users = DB::table('user_library')
							->where('user_id', $user_id)
							->where('category_id', $cid)							 
							->get();
							return $used_sub=count($users);
		//return $bal=$subcription_tot-$used_sub;
	 }
	 
	
	 // At A Time User Subsctiption Total Used 
	 public static function add_user_library($val,$m_c_id,$painting_type,$user_id) {
				$UserLibrary = new UserLibrary;
				$UserLibrary->product_id=$val;
				$UserLibrary->category_id=$m_c_id;
				$UserLibrary->painting_type=$painting_type;
				$UserLibrary->user_id=$user_id;			
				$UserLibrary->save();
	 }
	  
	 
	  // User limit total category wise
	// addlibrary($user_id,$cart,$bal,$value,$key)
	// public static function addlibrary($user_id,$cart,$audio_tot,$photos_tot,$digital_arts_tot,$videos_tot,$standard_tot,$premium_tot,$subcription_tot) {
	 public static function addlibrary($user_id,$cart,$bal,$value,$key) {	 
$data='';$success_data='';
							foreach($cart as $val => $arr)
											{			
												
												$user_library = DB::table('user_library')
												 ->where('product_id', $val)
													->where('user_id',$user_id)
												 ->first();
												 //dd(DB::GetQueryLog($user_library));
												 //exit;
														 if(empty($user_library))
														 {
										
										 
			 
															 //Add Library
															 
															$type = DB::table('products')
																->leftJoin('users', 'users.id', '=', 'products.merchant_id')
																->where('products.id', $val)
																->first();
																
																if($type)
										{																
																																	
																		self::add_user_library_other($val,$type->m_c_id,$user_id,$type->merchant_id);
																		$success_data='Successfully added in your Library';				
																	
											}									
																
																
																				
													
															$cartData = session()->get('cart');

																	if (array_key_exists($val, $cartData)) {
																		unset($cartData[$val]);
																	}
																	session()->put('cart', $cartData);
																	$cartTotal = 0;
																	foreach ($cartData as $cartItem) {
																		$cartTotal = $cartTotal+$cartItem['qty'];
																	}

															session()->put('total', $cartTotal);
														 }else{	
															$products = DB::table('products')
															->where('id', $val)	 
															->first();														
															 $data.=$products->name.' already added,';														 
														 }
														
												//sleep(2);
											}
											$data = $success_data.'<br />'.$data;
											return $data;
											
	 } 

	 // User limit total Painting Standard and Premium Check
	 public static function user_limit_total_category_painting($user_id,$tot,$cid,$type) {
				 
							$users = DB::table('user_library')
							->where('user_id', $user_id)
							->where('category_id', $cid)
							->where('painting_type', $type)							
							->get();
							//dd(DB::getQueryLog());
						return	$used_sub=count($users);
			//return $bal=$tot-$used_sub;
		 
	 } 
	 
    public function add(Request $request)
    {
		
		$this->validate($request,
			 [
             'firstname' => 'required',
            'lastname' => 'required',
            'street' => 'required',
            'city' => 'required',
            'psc' => 'required',
            'email' => 'required|email',
            'phone' => 'required'
				
               
            ],
            [
             'firstname' => 'First Name is required',
            'lastname' => 'Last Name is required',
            'street' => 'Street is required',
            'city' => 'City is required',
            'psc' => 'Pincode is required',
            'email' => 'Email is required',
            'phone' => 'Contact number is required',
           	
               
            ]);
			
			
  /*      $this->validate($request, [
            'firstname' => 'required',
            'lastname' => 'required',
            'street' => 'required',
            'city' => 'required',
            'psc' => 'required',
            'email' => 'required|email',
            'phone' => 'required'
        ]);
*/

                $orders = new Orders;
				$orders->c_email=$request->email;
				$orders->c_phone=$request->phone;
				$orders->b_pincode=$request->psc;
				$orders->b_city=$request->city;
				$orders->b_address_1=$request->street;
				$orders->payment_type='Cash on Deleivery';
				$orders->save();
			    $orders_id=$orders->id;	
			
       /* $customer = new orders($request->all());
        $customer->save();
        $customer_id = $customer->id;
        $order = new Order(['user_id' => $customer_id, 'status' => 0]);
        $order->save();
        $order_id = $order->id;*/

        $cartData = $request->session()->get('cart');

        $email_data = array();
        $id = 1;
        foreach ($cartData as $key => $value) {
            $product = Products::where('id', '=', $key)->get()->toArray();
            $price = $product['0']['price'];
			
			 $OrderProduct = new OrderProduct;			
				$OrderProduct->merchant_id=1;
				$OrderProduct->product_id=$key;
				$OrderProduct->order_id=$orders_id;
				$OrderProduct->qty=$value['qty'];
				$OrderProduct->total=$price;
				$OrderProduct->save();
				
           /* $order_product = new OrderProduct([
                'merchant_id' => 1,
                'product_id' => $key,
				'order_id' => $orders_id,			
                'qty' => $value['qty'],
                'total' => $price,
            ]);
            $order_product->save();
			dd(DB::getQueryLog());*/
         //   $email_data[$id]['product'] = $product['0']['name'];
         //   $email_data[$id]['qty'] = $value['qty'];
         //   $email_data[$id]['total'] = $price;
            $id++;
        }

        session()->forget('cart');

    //    Mail::to('mar.don@seznam.cz')->send(new NewOrder($email_data, $customer));

        return redirect('/')->with('message', 'Order Created Successfully');
    }
}
