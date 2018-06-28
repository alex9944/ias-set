<?php

namespace App\Http\Controllers;
use DB;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Attributes;
use App\Models\AttributesValues;
use App\Models\MultiLanguage;
use App\Http\Controllers\Controller;
use Image;


class AttributesValuesController extends Controller
{
    //
	public function index()
    {		
	  
	   $attributes= Attributes::all();
	 //  $multilanguage= MultiLanguage::all();
	  $attributesvalues= AttributesValues::all();	    
       return view('panels.admin.attributes.values',['attributes'=>$attributes,'attributesvalues'=>$attributesvalues,'edit_attributesvalues'=>$attributesvalues,'edit_attributes'=>$attributes,'edit_attr'=>$attributes,]);
    }
	
	public function getvalues(Request $request)
		{
			 $id=$request->id;  
			 $values = DB::table('products_attributes_values')
			 ->leftJoin('products_attributes', 'products_attributes.id', '=', 'products_attributes_values.av_a_id')
			 ->where('av_a_id', $id)	 
			 ->get();   
		 return '{"view_details": ' . json_encode($values) . '}';
	}

	public function added(Request $request)
	{
		 
			$this->validate($request,
			 [
			 
			    'av_id'            	=> 'required',
                'title'            	=> 'required',
                             
               
            ],
            [
			
			'av_id.required'   	=> 'Choose attributes is required',
            'title.required'   	=> 'Attributes value is required',                         
               
            ]);			
		
				$attributesvalues = new AttributesValues;
				$attributesvalues->av_title=$request->title;
				$attributesvalues->av_a_id=$request->av_id;				
				$attributesvalues->save();
			return redirect('admin/attributes/values')->with('message','Attributes values added successfully');
	}
	public function updated(Request $request)
	{	 
			/*$this->validate($request,
			 [
				'c_type'            	=> 'required',
                'c_title'            	=> 'required',                           
               
            ],
            [
				'c_type.required'   	=> 'Type is required',
                'c_title.required'   	=> 'Title is required',
                    
               
            ]);			
		*/
		//$cnt=count(att_id);
		foreach($request->att_id as $val){	
$atval='title'.$val;		
			DB::table('products_attributes_values')
				->where('av_id', $val)
				->update(['av_title' => $request->$atval,]);
		}
				

	return redirect('admin/attributes/values')->with('message','Attributes Values updated successfully');
	}


	public function deleted(Request $request)
	{	 
		$id=$request->id;  
		 $category = DB::table('products_attributes_values')
		 ->where('av_id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $status['deletedtatus']=' Attributes values deleted successfully ';
	 return '{"delete_details": ' . json_encode($status) . '}'; 
	
	}
	
	

	public function destroy(Request $request)
		{
			echo $cn=count($request->selected_id);
			if($cn>0)
			{
			$data = $request->selected_id;			
				foreach($data as $id) {
				DB::table('category')->where('c_id', $id)->delete();			
				}			
			} 
	return redirect('admin/category')->with('message','Seltected category are deleted successfully');			

	}
}