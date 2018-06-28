<?php

namespace App\Http\Controllers;
use DB;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\AdsPricing;
use App\Models\AdsPosition;
use App\Models\AdsDuration;
use App\Models\AdsModeAdvertisement;
use App\Models\AdsSlotPlacement;

use App\Http\Controllers\Controller;



class AdsPricingController extends Controller
{
    //
	public function index()
    {		
	   //$pricing= AdsPricing::all();
	   $adsposition= AdsPosition::all();
	   $duration= AdsDuration::all();
	   $modeads= AdsModeAdvertisement::all();
	   $slotplacement= AdsSlotPlacement::all();
	   
	   $pricing = DB::table('ads_pricing')
		->select('*', 'ads_pricing.id as pid','ads_mode_advertisement.title as amd_title','ads_position.title as aptitle')
		->leftJoin('ads_mode_advertisement', 'ads_pricing.m_a_id', '=', 'ads_mode_advertisement.id')
		->leftJoin('ads_position', 'ads_pricing.a_p_id', '=', 'ads_position.id')		
		->get();
	   
       return view('panels.admin.ads.pricing',['pricing'=>$pricing,'adsposition'=>$adsposition,'duration'=>$duration,'modeads'=>$modeads,'slotplacement'=>$slotplacement, 'editadsposition'=>$adsposition,'edit_adsposition'=>$adsposition,'edit_duration'=>$duration,'edit_modeads'=>$modeads,'edit_slotplacement'=>$slotplacement,]);
    }
	
	public function getpricing(Request $request)
		{
			 $id=$request->id;  
			 $pricing = DB::table('ads_pricing')
			 ->where('id', $id)	 
			 ->first();   
		 return '{"view_details": ' . json_encode($pricing) . '}';
	}
		
	public function added(Request $request)
	{
		
		$this->validate($request,
			 [
                'price'            	=> 'required',               
               
            ],
            [
                'price.required'   	=> 'Price is required',
                          
               
            ]);
				$pricing = new AdsPricing;				
				$pricing->m_a_id=$request->modeads;
				$pricing->a_p_id=$request->adsposition;
				$pricing->a_s_p_id=$request->slotplacement;
				$pricing->d_id=$request->duration;	
				$pricing->price=$request->price;
									
				$pricing->save();
			return redirect('admin/ads/pricing')->with('message','Pricing added successfully');
	}
	public function updated(Request $request)
	{	 
			
			$this->validate($request,
			 [
                'price'            	=> 'required',               
               
            ],
            [
                'price.required'   	=> 'Price is required',
                          
               
            ]);
			
				
				DB::table('ads_pricing')
				->where('id', $request->id)
				->update([				
				'm_a_id'=>$request->modeads,
				'a_p_id'=>$request->adsposition,
				'a_s_p_id'=>$request->slotplacement,
				'd_id'=>$request->duration,	
				'price'=>$request->price,
				]);

	return redirect('admin/ads/pricing')->with('message','Pricing updated successfully');
	}


	public function deleted(Request $request)
	{	 
	     $id=$request->id;  
		 $pricing = DB::table('ads_pricing')
		 ->where('id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $status['deletedtatus']='Pricing deleted successfully';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}

	public function destroy(Request $request)
		{
			$cn=count($request->selected_id);
			if($cn>0)
			{
			$data = $request->selected_id;			
				foreach($data as $id) {
				DB::table('ads_pricing')->where('id', $id)->delete();			
				}			
			} 
	return redirect('admin/ads/pricing')->with('message','Seltected mode pricing are deleted successfully');			

	}
}