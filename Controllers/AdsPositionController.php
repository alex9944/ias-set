<?php

namespace App\Http\Controllers;
use DB;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\AdsPosition;
use App\Http\Controllers\Controller;



class AdsPositionController extends Controller
{
    //
	public function index()
    {		
	   $adsposition= AdsPosition::all();
       return view('panels.admin.ads.adsposition',['adsposition'=>$adsposition]);
    }
	
	public function getposition(Request $request)
		{
			 $id=$request->id;  
			 $adsposition = DB::table('ads_position')
			 ->where('id', $id)	 
			 ->first();   
		 return '{"view_details": ' . json_encode($adsposition) . '}';
	}
		
	public function added(Request $request)
	{
		
		$this->validate($request,
			 [
                'title'            	=> 'required',               
               
            ],
            [
                'title.required'   	=> 'Position Title is required',
                          
               
            ]);
				$adsposition = new AdsPosition;				
				$adsposition->title=$request->title;				
				$adsposition->save();
			return redirect('admin/ads/position')->with('message','Position added successfully');
	}
	public function updated(Request $request)
	{	 
			
			$this->validate($request,
			 [
                'title'            	=> 'required',               
               
            ],
            [
                'title.required'   	=> 'Position Title is required',
                          
               
            ]);
			
				
				DB::table('ads_position')
				->where('id', $request->id)
				->update(['title' => $request->title,]);

	return redirect('admin/ads/position')->with('message','Position updated successfully');
	}


	public function deleted(Request $request)
	{	 
	     $id=$request->id;  
		 $ads_position = DB::table('ads_position')
		 ->where('id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $status['deletedtatus']='Position deleted successfully';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}

	public function destroy(Request $request)
		{
			$cn=count($request->selected_id);
			if($cn>0)
			{
			$data = $request->selected_id;			
				foreach($data as $id) {
				DB::table('ads_position')->where('id', $id)->delete();			
				}			
			} 
	return redirect('admin/ads/position')->with('message','Seltected Position are deleted successfully');			

	}
}