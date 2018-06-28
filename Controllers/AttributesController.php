<?php

namespace App\Http\Controllers;
use DB;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Attributes;
use App\Http\Controllers\Controller;



class AttributesController extends Controller
{
    //
	public function index()
    {		
	   $attributes= Attributes::all();
       return view('panels.admin.attributes.index',['attributes'=>$attributes]);
    }
	
	public function getcategoryslug(Request $request)
		{
			 $id=$request->id;  
			 $products_attributes = DB::table('products_attributes')
			 ->where('id', $id)	 
			 ->first();   
		 return '{"view_details": ' . json_encode($products_attributes) . '}';
	}
		
	public function added(Request $request)
	{
		
		$this->validate($request,
			 [
                'title'            	=> 'required',               
               
            ],
            [
                'title.required'   	=> 'Title is required',                             
               
            ]);
			
			
				$attributes = new Attributes;				
				$attributes->title=$request->title;
				$attributes->a_type=$request->a_type;				
				$attributes->save();
			return redirect('admin/attributes')->with('message','Attributes added successfully');
	}
	public function updated(Request $request)
	{	 
			
			$this->validate($request,
			 [
                'title'            	=> 'required',               
               
            ],
            [
                'title.required'   	=> 'Title is required',                             
               
            ]);
			
				
				DB::table('products_attributes')
				->where('id', $request->id)
				->update(['title' => $request->title,'a_type' => $request->a_type,]);

	return redirect('admin/attributes')->with('message','Attributes updated successfully');
	}


	public function deleted(Request $request)
	{	 
	     $id=$request->id;  
		 $slug = DB::table('products_attributes')
		 ->where('id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $status['deletedtatus']='Attributes deleted successfully';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}

	public function destroy(Request $request)
		{
			$cn=count($request->selected_id);
			if($cn>0)
			{
			$data = $request->selected_id;			
				foreach($data as $id) {
				DB::table('products_attributes')->where('id', $id)->delete();			
				}			
			} 
	return redirect('admin/attributes')->with('message','Seltected Attributes are deleted successfully');			

	}
}