<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\SubscriptionPricing;
use App\Models\Country;
use App\Models\Currency;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionDuration;
use App\Models\SubscriptionFeatures;
use App\Models\MerchantsType;

class ApiSubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	
			/*$data['pricing'] = DB::table('subscription_pricing')
			->select('subscription_pricing.id as pid','f_value','price','title','subscription_pricing.subscription_flag')
			->leftJoin('countries', 'countries.id', '=', 'subscription_pricing.c_id')
			->leftJoin('subscription_plan', 'subscription_plan.id', '=', 'subscription_pricing.p_id')
			->leftJoin('currency', 'currency.id', '=', 'subscription_pricing.cur_id')			
			->get();
			*/
			
			$data['pricing'] = DB::table('subscription_pricing')
			->select('subscription_pricing.id as pid','subscription_plan.title as ptitle','subscription_pricing.f_value as f_value','subscription_pricing.f_id as f_id','subscription_duration.title as duration','subscription_duration.days_month as days_month_year')
			->leftJoin('countries', 'countries.id', '=', 'subscription_pricing.c_id')
			->leftJoin('subscription_plan', 'subscription_plan.id', '=', 'subscription_pricing.p_id')
			->leftJoin('subscription_duration', 'subscription_duration.id', '=', 'subscription_pricing.d_id')
			->leftJoin('currency', 'currency.id', '=', 'subscription_pricing.cur_id')->where('subscription_pricing.subscription_flag','1')		
			->get();
			
			$data['features'] = MerchantsType::all();
			
			
			//$data['features'] = SubscriptionFeatures::all();
			
      $responsecode = 200; 	  
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

    }

 
}
