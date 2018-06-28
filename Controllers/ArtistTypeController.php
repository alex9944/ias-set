<?php

namespace App\Http\Controllers;
use DB;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\MerchantsType;
use App\Http\Controllers\Controller;
use Image;


class ArtistTypeController extends Controller
{
    //
	public function index()
    {		
	  // $category= Category::all();
	   $artisttype= MerchantsType::all();
	   
       return view('panels.admin.merchants.type',['artisttype'=>$artisttype,'user_type'=>$artisttype,'edit_user_type'=>$artisttype,'edit_artisttype'=>$artisttype,]);
    }

	public function gettype(Request $request)
		{
			 $id=$request->id;  
			 $groups = DB::table('merchants_type')
			 ->where('id', $id)	 
			 ->first();   
		 return '{"view_details": ' . json_encode($groups) . '}';
	}
		
	public function added(Request $request)
	{
		 
			$this->validate($request,
			 [
			 
			    
                'title'            	=> 'required',
				'commission'            	=> 'required',
                             
               
            ],
            [
			
			
                'title.required'   	=> 'Title is required',
                'commission.required'   	=> 'Commission is required',  								
               
            ]);
			
			
				$groups = new MerchantsType;
				$groups->title=$request->title;						
				$groups->commission=$request->commission;				
				$groups->save();
			return redirect('admin/merchants/type')->with('message','Merchants type added successfully');
	}
	public function updated(Request $request)
	{	 
			$this->validate($request,
			 [
		
                'title'            	=> 'required',	
                'commission'            	=> 'required',								
               
            ],
            [				
                'title.required'   	=> 'Title is required',	
				'commission.required'   	=> 'Commission is required',  								
                				
               
            ]);
			
			
			
				DB::table('merchants_type')
				->where('id', $request->id)
				->update(['title' => $request->title,'commission' => $request->commission,]);

	return redirect('admin/merchants/type')->with('message','Merchants type updated successfully');
	}


	public function deleted(Request $request)
	{	 
	$id=$request->id;  
		 $category = DB::table('merchants_type')
		 ->where('id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $status['deletedtatus']='Merchants type deleted successfully';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}

	public function destroy(Request $request)
		{
			$cn=count($request->selected_id);
			if($cn>0)
			{
			$data = $request->selected_id;			
				foreach($data as $id) {
				DB::table('merchants_type')->where('id', $id)->delete();			
				}			
			} 
	return redirect('admin/merchants/type')->with('message','Selected merchants type are deleted successfully');			

	}
}