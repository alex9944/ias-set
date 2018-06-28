<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests;

use App\Models\ArtistSocialLinks;
use App\Models\User;
use App\Models\Role;
use App\Models\UserAccessModules;
use App\Traits\ActivationTrait;
use App\Http\Controllers\Controller;
use App\Models\MultiLanguage;
use App\Models\GeneralUserType;
use App\Models\MerchantProdAttributes;
use Illuminate\Support\Facades\Auth;
use Image;


class ArtistSocialLinksController extends Controller
{
    //
	public function index()
    {		
	   $artistsociallinks= ArtistSocialLinks::all();
	 
		$category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();
       return view('panels.artist.sociallinks',['category'=>$category,'artistsociallinks'=>$artistsociallinks]);
    }

	public function getartistsociallinks(Request $request)
		{
			
			$user_id= Auth::user()->id;
	        
			 $id=$request->id;  
			 $data['artistsociallinks'] = DB::table('artistsociallinks')
			 ->where('id', $id)	 
			 ->first();
           /*  $data['att'] = DB::table('merchant_prod_attributes')
			 ->where('p_id', $id)	 
			 ->get(); */
			 
		 return '{"view_details": ' . json_encode($data) . '}';
	}
	
	
		
	public function artistsociallinks_added(Request $request)
	{
		
		
		  $this->validate($request,
			 [
			 
					
			'title'			=> 'required',			
			'icon'			=> 'required',	
			'url'	        => 'required',		
               
            ],
            [
					
			'title.required'			=> 'Name is required',			
			'icon.required'			    => 'Icon should be a text',
			'url.required'	            => 'URL is required',
                     ]);

				$user_id= Auth::user()->id;
	           
				
				$artistsociallinks = new artistsociallinks;				
			    $artistsociallinks->user_id=$user_id;					
				$artistsociallinks->title=$request->title;							
				$artistsociallinks->icon=$request->icon;
				$artistsociallinks->url=$request->url;
				
				$artistsociallinks->save();
				
				

			return redirect('artist/artist-social-links/view')->with('message','ArtistSocialLinks Added Successfully');
	}
	
	
	public function artistsociallinks_view(Request $request)
	{
		
		$user_id=Auth::user()->id;
		
		$artistsociallinks= ArtistSocialLinks::get()->where('user_id',$user_id);
			
		$category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();
	  return view('panels.artist.sociallinks_view',['category'=>$category,'artistsociallinks'=>$artistsociallinks]);
	}
	
	public function artistsociallinks()
	{
		
		$artistsociallinks= ArtistSocialLinks::all();
			 
	   //$user_id= $request->id;
	     
		//print_r($artistsociallinks);die;	
	  echo response()->json($artistsociallinks);
	}
	
     public function artistsociallinks_edit($id)
	 {
		 //$artistsociallinks= ArtistSocialLinks::all();
			 
	      $user_id= Auth::user()->id;
		 $artistsociallinks = DB::table('artistsociallinks')
			 ->where('id', $id)	 
			 ->first();
	   $category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();
		 
		 return view('panels.artist.sociallinks_edit',['category'=>$category,'artistsociallinks'=>$artistsociallinks,'id' =>$id]);
	 }
	
	
	public function updated($id,Request $request)
	{	
 $this->validate($request,
			 [
			 
			'title'			=> 'required',			
			'icon'			=> 'required',	
			'url'	        => 'required',		
               
            ],
            [
					
			'title.required'			=> 'Name is required',			
			'icon.required'			    => 'Icon should be a text',
			'url.required'	            => 'URL is required',
                     ]);
			
			$user_id= Auth::user()->id;
	            
			//$id=$request->id;
				DB::table('artistsociallinks')
				->where('id', $id)
				->update([					
				'title'=>$request->title,		
				'icon'=>$request->icon,	
                'url'=>$request->url,					
				]);
//dd(DB::getQueryLog());
			
				return redirect('artist/artist-social-links/view')->with('message','ArtistSocialLinks Updated Successfully');
	}
	public function enable(Request $request)
		{	 
		$id=$request->id;
		DB::table('artistsociallinks')
		->where('id', $request->id)
		->update(['status' => 'Disable',]);
		$status['deletedtatus']='ArtistSocialLinks Status Updated Successfully';
		 return '{"delete_details": ' . json_encode($status) . '}'; 
		
		}
	public function disable(Request $request)
		{	 
		$id=$request->id;
		DB::table('artistsociallinks')
		->where('id', $request->id)
		->update(['status' => 'Enable',]);
		$status['deletedtatus']='ArtistSocialLinks Status Updated Successfully';
		 return '{"delete_details": ' . json_encode($status) . '}'; 
		
		}

	public function deleted(Request $request)
		{	 
		$id=$request->id;  
			 $blogs = DB::table('artistsociallinks')
			 ->where('id', $id)
			 ->delete();
			 $status['deletedid']=$id;
			 $status['deletedtatus']='ArtistSocialLinks Deleted Successfully';
		 return '{"delete_details": ' . json_encode($status) . '}'; 
		
		}

	public function destroy(Request $request)
	{
			$cn=count($request->selected_id);
			if($cn>0)
			{
			$data = $request->selected_id;			
				foreach($data as $id) {
				DB::table('artistsociallinks')->where('id', $id)->delete();			
				}			
			} 
		return redirect('panels.artist.sociallinks')->with('message','Selected ArtistSocialLinks are Deleted Successfully');			

	}
}