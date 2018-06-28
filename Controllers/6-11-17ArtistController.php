<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use App\Models\Role;
use App\Models\UserAccessModules;
use App\Traits\ActivationTrait;
use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\MultiLanguage;
use App\Models\Country;
use App\Models\States;
use App\Models\Cities;
use App\Models\Category;
use App\Models\CategorySlug;
use App\Models\Attributes;
use App\Models\AttributesValues;
use App\Models\GeneralUserType;
use App\Models\MerchantProdAttributes;
use Image;
use DB;
use Illuminate\Support\Facades\Auth;
use FFMpeg\FFMpeg;

class ArtistController extends Controller {

    public function index()
    {
		$category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();		
        return view('panels.artist.home',['category'=>$category,]);
    }
	 public function subscription()
    {
			$user_id= Auth::user()->id;
			
	$category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();

$artiest_product_subscription = DB::table('artiest_product_subscription')
		->select('*', 'artiest_product_subscription.id as sid','artiest_product_subscription.status as sta')
		->leftJoin('products', 'products.id', '=', 'artiest_product_subscription.product_id')
		 ->where('artiest_user_id', $user_id)
		->get();


					 
        return view('panels.artist.subscription',['category'=>$category,'artiest_product_subscription'=>$artiest_product_subscription,]);
    }
	
	 public function profile()
    {	
	$user_id= Auth::user()->id;
	$users = DB::table('users')		
		->where('id', '=', $user_id)
		->first();
     $Country= Country::all();
	 $editstates = DB::table('states')			 
			 ->get();
		$editcities = DB::table('cities')			 
			 ->get();
		$category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();	 
        return view('panels.artist.profile',['category'=>$category,'users'=>$users,'Country'=>$Country,'editstates' => $editstates,'editcities' => $editcities]);
    }
	public function getstates(Request $request)
		{
			 $id=$request->id;  
			 $states = DB::table('states')
			 ->where('country_id', $id)	 
			 ->get();   
		 return '{"view_details": ' . json_encode($states) . '}';
	}
	
	public function getcities(Request $request)
		{
			 $id=$request->id;  
			 $cities = DB::table('cities')
			 ->where('state_id', $id)	 
			 ->get();   
		 return '{"view_details": ' . json_encode($cities) . '}';
	}
	 public function password()
    {	
	$user_id= Auth::user()->id;
	$users = DB::table('users')		
		->where('id', '=', $user_id)
		->first();
		$category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();	
        return view('panels.artist.password',['category'=>$category,'users'=>$users]);
    }
	//Admin Users Display
	 public function users()
    {		
	//Get Users Details
	    $users = DB::table('users')
		->select('*', 'users.id as uid')
		->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
		->where('role_user.role_id', '=', 2)
		->get();
		$category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();	
        return view('panels.admin.users', ['category'=>$category,'users' => $users]);
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
	
	public function updated(Request $request)
	{	 
	
	$this->validate($request,
			 [
                'firstname'            	=> 'required|regex:/^[\pL\s\-]+$/u',
                'lastname'             	=> 'required|regex:/^[\pL\s\-]+$/u',
				'phone_no'             	=> 'required',
				'dob'             	=> 'required',
				'competencies'             	=> 'required',
'address'             	=> 'required',	
'country'             	=> 'required',	
'states'             	=> 'required',	
'cities'             	=> 'required',	
'postcode'             	=> 'required',
'description'             	=> 'required',			
            ],
            [
                'firstname.required'   	=> 'First Name is required',
                'lastname.required'    	=> 'Last Name is required',
				'lastname.required'    	=> 'Last Name is required',
                'phone_no.required'     => 'Contact no is required',
				'dob'             	=> 'Date of birth is required',
				'competencies'             	=> 'Core competencies is required',
				'address'             	=> 'Address is required',
				'country'             	=> 'Country is required',
				'states'             	=> 'States is required',
				'cities'             	=> 'Cities is required',
				'postcode'             	=> 'Postcode is required',
				'description'             	=> 'Description is required',				
                      
               
            ]);
			
			$photo = $request->file('photo');
			 if($photo){
					$imagename = time().'.'.$photo->getClientOriginalExtension();   
					$destinationPath = public_path('uploads\thumbnail');
					$thumb_img = Image::make($photo->getRealPath())->resize(100, 100);
					$thumb_img->save($destinationPath.'/'.$imagename,80);                    
					$destinationPath = public_path('uploads\original');
					$photo->move($destinationPath, $imagename);
						 DB::table('users')
						->where('id', $request->id)
						->update(['image' => $imagename,]);
			 }
			 $banner = $request->file('banner');
			  if($banner){
					$bannerimg = time().'.'.$banner->getClientOriginalExtension();   
					$destinationPath = public_path('uploads\banner\thumbnail');
					$thumb_img = Image::make($banner->getRealPath())->resize(100, 100);
					$thumb_img->save($destinationPath.'/'.$bannerimg,80);                    
					$destinationPath = public_path('uploads\banner\original');
					$banner->move($destinationPath, $bannerimg);
						 DB::table('users')
						->where('id', $request->id)
						->update(['banner' => $bannerimg,]);
			 }
			
		
				DB::table('users')
				->where('id', $request->id)	
				->update(['admin_type'=>$request->admin_type,'first_name' => $request->firstname,'last_name' => $request->lastname,'phone_no' => $request->phone_no,'dob' => $request->dob,'competencies' => $request->competencies,'address' => $request->address,'country' => $request->country,'states' => $request->states,'city' => $request->cities,'postcode' => $request->postcode,'work_ling' => $request->work_ling,'description' => $request->description,]);

	return redirect('artist/profile')->with('message','User profile updated successfully');
	}
	
	public function pupdated(Request $request)
	{	 
	
	$this->validate($request,
			 [
           'password'            	=> 'required|min:6',
                'cpassword'             	=> 'required|min:6|same:password',                    
               
            ],
            [
                'password.required'   	=> 'Password is required',
                'cpassword.required'    	=> 'Confirm Password required',
				'cpassword.same'    	=> 'Password donot match'         
                          
               
            ]);			
			
			
			if($request->password)
			{
				 DB::table('users')
					->where('id', $request->id)
					->update(['password' => bcrypt($request->password),]);				
			}				
	return redirect('artist/password')->with('message','User password updated successfully');
	}

public function artworks_add($id)
    {	
	 
	   $products= products::all();
	   $attributes= Attributes::all();
	   $language= MultiLanguage::all();
	   $country= Country::all();
	   $generalusertype= GeneralUserType::all();
	   
	   //$category= Category::all();
	  $category = DB::table('category')
					 ->where('parent_id', '0')	 
					 ->get();	
			
		$user_id = Auth::user()->id;
        $users = DB::table('users')
		->where('id', '=' , $user_id)
		->first();
			
	    
	    $users = DB::table('users')
			->select('*', 'users.id as uid')
			->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
			->where('role_user.role_id', '=', 3)
			->get();
		
		
		$subcategory = DB::table('category')
			 ->where('parent_id','!=', '0')
			 ->get();
		$editstates = DB::table('states')			 
			 ->get();
		$editcities = DB::table('cities')			 
			 ->get();
			  
			 $cat = DB::table('category')
			->where('c_id', '=' , $id)
			->first(); 
			  
			 $pcategory = DB::table('category')
			->where('parent_id', '=' , $id)			 
			 ->get(); 
			//dd(DB::getQueryLog());
			  if($cat->c_title=='Audio'){
				  //Audio Providers
			$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
			
        return view('panels.artist.artworks_audio',['id'=>$id,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title'=>$cat->c_title]);
			  }elseif($cat->c_title=='Paintings'){
				  //Painters
			$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
        return view('panels.artist.artworks_painters',['id'=>$id,'pcategory'=>$pcategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'scategory' => $scategory,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title'=>$cat->c_title]);  
				  
			  }
			  elseif($cat->c_title=='Photography'){
				  
				  //Photographers
		$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
        return view('panels.artist.artworks_photographers',['id'=>$id,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title'=>$cat->c_title]);
				  
			  }
			  elseif($cat->c_title=='Videos'){
				  
				 //Video Providers
		$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
        return view('panels.artist.artworks_video',['id'=>$id,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title'=>$cat->c_title]); 
			  }
			  else{
				  //Digital Artists
		$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
        return view('panels.artist.artworks_digital_art',['id'=>$id,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title'=>$cat->c_title]);  
				  
			  }
			
    }
	
	
	public function digital_arts_added(Request $request)
	{
		
		
		  $this->validate($request,
			 [
			 
				
			's_c_id'		=> 'required',			
			'title'			=> 'required',			
			'photo'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg',	
			'description'	=> 'required',		
               
            ],
            [		
			's_c_id.required'		=> 'Category is required',			
			'title.required'			=> 'Title is required',			
			'photo.required'			=> 'Image should be a jpg,png,gif',
			'photo.image'           	=>  'required|image|mimes:jpeg,png,jpg,gif,svg',					
			'description.required'	=> 'required',
                     ]);
			
				$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
				
				
                $user_id = Auth::user()->id;             
				$products = new products;				
				$products->merchant_id=$user_id;				
				$products->m_c_id=$request->category;
				$products->s_c_id=$request->s_c_id;				
				$products->name=$request->title;							
				$products->image=$imagename;				
				$products->description=$request->description;				
				$products->save();				
				$id=$products->id;	
		

			return redirect('artist/artworks/add/'.$request->cat_id)->with('message',$request->category_title.' added successfully');
	}
	public function digital_arts_edit(Request $request)
	{
		
		
		
		  $this->validate($request,
			 [			
			's_c_id'		=> 'required',		
			'title'			=> 'required',
			'description'	=> 'required',		
               
            ],
            [
			's_c_id.required'		=> 'Category is required',			
			'title.required'			=> 'Title is required',					
			'description.required'	=> 'required',
                     ]);
			
				$photo = $request->file('photo');
			 if($photo){
					$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
						  DB::table('products')
						->where('id', $request->id)
						->update(['image' => $imagename,]);
			 }		
				
                $id=$request->id;
				DB::table('products')
				->where('id', $request->id)
				->update([
				's_c_id'=>$request->s_c_id,				
				'name'=>$request->title,				
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view/'.$request->cat_id)->with('message',$request->category_title.' updated successfully');
	}
	
	
	//Photographers
	
	public function photographers_added(Request $request)
	{
		
		
		
		  $this->validate($request,
			 [
			 
				
			's_c_id'		=> 'required',			
			'title'			=> 'required',			
			'photo'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg',	
			'description'	=> 'required',		
               
            ],
            [		
			's_c_id.required'		=> 'Category is required',			
			'title.required'			=> 'Title is required',			
			'photo.required'			=> 'Image should be a jpg,png,gif',
			'photo.image'           	=>  'required|image|mimes:jpeg,png,jpg,gif,svg',					
			'description.required'	=> 'required',
                     ]);
			
				$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
				
				
                $user_id = Auth::user()->id;             
				$products = new products;				
				$products->merchant_id=$user_id;				
				$products->m_c_id=$request->category;
				$products->s_c_id=$request->s_c_id;				
				$products->name=$request->title;							
				$products->image=$imagename;				
				$products->description=$request->description;				
				$products->save();				
				$id=$products->id;	
		

			return redirect('artist/artworks/add/'.$request->cat_id)->with('message',$request->category_title.' added successfully');
	}
	public function photographers_edit(Request $request)
	{
		
		
		
		  $this->validate($request,
			 [			
			's_c_id'		=> 'required',		
			'title'			=> 'required',
			'description'	=> 'required',		
               
            ],
            [
			's_c_id.required'		=> 'Category is required',			
			'title.required'			=> 'Title is required',					
			'description.required'	=> 'required',
                     ]);
			
				$photo = $request->file('photo');
			 if($photo){
					$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
						  DB::table('products')
						->where('id', $request->id)
						->update(['image' => $imagename,]);
			 }		
				
                $id=$request->id;
				DB::table('products')
				->where('id', $request->id)
				->update([
				's_c_id'=>$request->s_c_id,				
				'name'=>$request->title,				
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view/'.$request->cat_id)->with('message',$request->category_title.' updated successfully');
	}
	
	////End Photographers
	
	
	// Audio 
	

	
	public function audio_added(Request $request)
	{
		
		
		
		  $this->validate($request,
			 [ 
				
			's_c_id'			=> 'required',		
			'title'			=> 'required',			
			'audio'			=> 'required',	
			'description'	=> 'required',		
               
            ],
            [		
			's_c_id.required'			=> 'Category is required',			
			'title.required'			=> 'Title is required',
			'audio.required'			=> 'Audio file is required',			
			'description.required'	=> 'required',
                     ]);
			    $img=time(); 
				$audio = $request->file('audio');
				$imagename = $img.'.'.$audio->getClientOriginalExtension();
				$path = public_path('uploads/original-audios/');
				$audio->move($path, $imagename);
				$imgpath = public_path('uploads/audio-images/');				
				$dpath = public_path('uploads/audios/');
				$public_path = public_path();	
				$ffmpeg = FFMpeg::create([
				'ffmpeg.binaries' => 'C:/ffmpeg/bin/ffmpeg.exe',
				'fprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe']);
						
				$video = $ffmpeg->open($path.$imagename);

				// Set an audio format
				$audio_format = new \FFMpeg\Format\Audio\Mp3();

				// Extract the audio into a new file
				$video->save($audio_format,$dpath.'/'.$img.'.mp3');

				// Set the audio file
				//$audio = $ffmpeg->open($dpath.'/'.$img.'.mp3');

				// Create the waveform
				//$waveform = $audio->waveform();
				//$waveform->save($dpath.'/'.$img.'.jpg' );		
				/*   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($imgpath.$img.'.jpg')->fit(214);
				$thumb_img->save($destinationPath.'/'.$img.'.jpg',80);
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				$image=$img.'.jpg';
			   $thumb_img = Image::make($imgpath.$img.'.jpg')->fit(543, 343, function ($constraint) 
							{
									$constraint->upsize();
									});
			   $thumb_img->save($destinationPathbig.'/'.$img.'.jpg',80);	*/						
				
				
                $user_id = Auth::user()->id;             
				$products = new products;				
				$products->merchant_id=$user_id;			
				
				$products->m_c_id=$request->category;
				$products->s_c_id=$request->s_c_id;				
				$products->name=$request->title;
				$products->audio_url=$img.'.mp3';				
				$products->image='audio.jpg';				
				$products->description=$request->description;				
				$products->save();
				
				$id=$products->id;	
		

			return redirect('artist/artworks/add/'.$request->cat_id)->with('message',$request->category_title.' added successfully');
	}
	public function audio_edit(Request $request)
	{
		
		
		
		  $this->validate($request,
			 [			
			
			'title'			=> 'required',			
			'description'	=> 'required',		
               
            ],
            [			
			'title.required'			=> 'Title is required',          	
			'description.required'	=> 'required',
                     ]);
			
				$audio = $request->file('audio');
			 if($audio){
				 $img=time(); 
				$audio = $request->file('audio');
				$imagename = $img.'.'.$audio->getClientOriginalExtension();
				$path = public_path('uploads/original-audios/');
				$audio->move($path, $imagename);
				$imgpath = public_path('uploads/audio-images/');				
				$dpath = public_path('uploads/audios/');
				$public_path = public_path();	
				$ffmpeg = FFMpeg::create([
				'ffmpeg.binaries' => 'C:/ffmpeg/bin/ffmpeg.exe',
				'fprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe']);
						
				$video = $ffmpeg->open($path.$imagename);

				// Set an audio format
				$audio_format = new \FFMpeg\Format\Audio\Mp3();

				// Extract the audio into a new file
				$video->save($audio_format,$dpath.'/'.$img.'.mp3');				
						  DB::table('products')
						->where('id', $request->id)
						->update(['audio_url' => $img.'.mp3']);
			 }		
				
                $id=$request->id;
				DB::table('products')
				->where('id', $request->id)
				->update(['s_c_id'=>$request->s_c_id,										
				'name'=>$request->title,
				'audio_url'=>$request->audio_url,				
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view/'.$request->cat_id)->with('message',$request->category_title.' updated successfully');
	}
	
	//Video

	
	public function video_added(Request $request)
	{
		//mp4,x-flv,x-mpegURL,MP2T,3gpp,quicktime,x-msvideo,x-ms-wmv
		//'video_url'			=> 'required',	
		//'video_url.required'			=> 'Video url is required',	
		
		  $this->validate($request,
			 [
			 
				
			//|mimes:mp4,avi,wmv,mov,3gp,flv,m3u8,ogx,oga,ogv,ogg,webm
			's_c_id'			=> 'required',			
			'title'			=> 'required',	
			'photo'			=> 'required',
			'description'	  => 'required',		
               
            ],
            [
			's_c_id.required'			=> 'Category is required',				
			'title.required'			=> 'Title is required',	
			'photo.required'			=> 'Video is required',
		//	'photo.video'           	=>  'required|video|mimes:webm',
						
			'description.required'	=> 'required',
                     ]);
					 
				$img=time(); 
				$photo = $request->file('photo');
				$imagename = $img.'.'.$photo->getClientOriginalExtension();
				
				$path = public_path('uploads/original-videos/');
				$photo->move($path, $imagename);
				$imgpath = public_path('uploads/videos-images/');				
				$dpath = public_path('uploads\videos');
				$public_path = public_path();	
				$ffmpeg = FFMpeg::create([
				'ffmpeg.binaries' => 'C:/ffmpeg/bin/ffmpeg.exe',
				'fprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe']);
						
						$video = $ffmpeg->open($path.$imagename);
						$video
							->filters()
							->resize(new \FFMpeg\Coordinate\Dimension(320, 240))
							->synchronize();
						$video
							->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(10))
							->save($imgpath.'/'.$img.'.jpg');
						$video   
							->save(new \FFMpeg\Format\Video\WebM(), $dpath.'\\'.$img.'.webm');
						$video 
							->save(new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'), $dpath."\\".$img.'.mp4');
						
				
		

   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($imgpath.$img.'.jpg')->fit(214);
				$thumb_img->save($destinationPath.'/'.$img.'.jpg',80);
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				$image=$img.'.jpg';
			   $thumb_img = Image::make($imgpath.$img.'.jpg')->fit(543, 343, function ($constraint) 
							{
									$constraint->upsize();
									});
							$thumb_img->save($destinationPathbig.'/'.$img.'.jpg',80);
// ->save(new \FFMpeg\Format\Video\WebM(), $dpath.'/export-webm.webm');
 //->save(new \FFMpeg\Format\Video\X264(), $dpath.'/export-x264.mp4')
			
				 //exec($public_path."\ffmpeg.exe"); // load ffmpeg.exe
				// exec("ffmpeg -i ".$path.$imagename." -vb 300k -s 432x336 -an ".$dpath.$imagename);
				// exec("ffmpeg -ss 00:00:02 -i ".$path.$imagename." -vf scale=800:-1 -vframes 1 ".$dpath.time().".jpg");
			
			/*
				$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);		
				*/
                $user_id = Auth::user()->id;             
				$products = new products;				
				$products->merchant_id=$user_id;				
				$products->m_c_id=$request->category;
				$products->s_c_id=$request->s_c_id;
				$products->name=$request->title;
				$products->video_url=$img.'.webm';				
				$products->image=$image;				
				$products->description=$request->description;				
				$products->save();				
				$id=$products->id;	
		

			return redirect('artist/artworks/add/'.$request->cat_id)->with('message',$request->category_title.' added successfully');
	}
	public function video_edit(Request $request)
	{
		
		 $this->validate($request,
			 [
			 
				
			's_c_id'			=> 'required',	
			'title'			=> 'required',						
			'description'	=> 'required',		
               
            ],
            [
			's_c_id.required'			=> 'Category is required',				
			'title.required'			=> 'Title is required',		
			'description.required'	=> 'Description is required',
                     ]);
					 
				
							
			
			
				$photo = $request->file('photo');
			 if($photo){
				$img=time(); 
				$photo = $request->file('photo');
				$imagename = $img.'.'.$photo->getClientOriginalExtension();
				$path = public_path('uploads/original-videos/');
				$photo->move($path, $imagename);
				$imgpath = public_path('uploads/videos-images/');				
				$dpath = public_path('uploads\videos');
				$public_path = public_path();	
				$ffmpeg = FFMpeg::create([
				'ffmpeg.binaries' => 'C:/ffmpeg/bin/ffmpeg.exe',
				'fprobe.binaries' => 'C:/ffmpeg/bin/ffprobe.exe']);
						
						$video = $ffmpeg->open($path.$imagename);
						$video
							->filters()
							->resize(new \FFMpeg\Coordinate\Dimension(320, 240))
							->synchronize();
						$video
							->frame(\FFMpeg\Coordinate\TimeCode::fromSeconds(10))
							->save($imgpath.'/'.$img.'.jpg');
						$video   
							->save(new \FFMpeg\Format\Video\WebM(), $dpath."\\".$img.'.webm');
						$video 
							->save(new \FFMpeg\Format\Video\X264('libmp3lame', 'libx264'), $dpath."\\".$img.'.mp4');
   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($imgpath.$img.'.jpg')->fit(214);
				$thumb_img->save($destinationPath.'/'.$img.'.jpg',80);
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				$image=$img.'.jpg';
			   $thumb_img = Image::make($imgpath.$img.'.jpg')->fit(543, 343, function ($constraint) 
							{
									$constraint->upsize();
									});
							$thumb_img->save($destinationPathbig.'/'.$img.'.jpg',80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
						  DB::table('products')
						->where('id', $request->id)
						->update(['image' => $image,'video_url' => $img.'.webm']);						
			 }		
				
                $id=$request->id;
				DB::table('products')
				->where('id', $request->id)
				->update(['s_c_id'=>$request->s_c_id,									
				'name'=>$request->title,
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view/'.$request->cat_id)->with('message',$request->category_title.' updated successfully');
	}
	
	public function added(Request $request)
	{
		
		$this->validate($request,
			 [
			 
				
			's_c_id'		=> 'required',			
			'title'			=> 'required',			
			'photo'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg',	
			'description'	=> 'required',		
               
            ],
            [		
			's_c_id.required'		=> 'Category is required',			
			'title.required'			=> 'Title is required',			
			'photo.required'			=> 'Image should be a jpg,png,gif',
			'photo.image'           	=>  'required|image|mimes:jpeg,png,jpg,gif,svg',					
			'description.required'	=> 'required',
                     ]);
			
				$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
				
				
                $user_id = Auth::user()->id;             
				$products = new products;				
				$products->merchant_id=$user_id;				
				$products->m_c_id=$request->category;
				$products->s_c_id=$request->s_c_id;				
				$products->name=$request->title;							
				$products->image=$imagename;				
				$products->description=$request->description;				
				$products->save();				
				$id=$products->id;	
		

			return redirect('artist/artworks/add/'.$request->cat_id)->with('message',$request->category_title.' added successfully');
		/*
		  $this->validate($request,
			 [
			 
				
			'category'		=> 'required',			
			'title'			=> 'required',			
			'photo'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg',	
			'description'	=> 'required',		
               
            ],
            [		
			'category.required'		=> 'Category is required',			
			'title.required'			=> 'Title is required',			
			'photo.required'			=> 'Image should be a jpg,png,gif',
			'photo.image'           	=>  'required|image|mimes:jpeg,png,jpg,gif,svg',			
			'description.required'	=> 'required',
                     ]);
			
				$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
		
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
				
                $user_id = Auth::user()->id;             
				$products = new products;				
				$products->merchant_id=$user_id;			
				$products->c_type=$request->c_type;
				$products->m_c_id=$request->category;
				$products->s_c_id=$request->scategory;				
				$products->price=$request->price;						
				$products->name=$request->title;							
				$products->image=$imagename;
				$products->meta_tag=$request->meta_tag;
				$products->meta_description=$request->meta_description;
				$products->description=$request->description;
				
				$products->discount_type=$request->discount_type;
				$products->discount_group=$request->group;
				$products->discount_amount=$request->amount;
				$products->discount_percentage_amt=$request->d_type;
				$products->discount_from_date=$request->from_date;
				$products->discount_to_date=$request->end_date;
				$products->discount_status=$request->status;
				
				$products->save();
				
				$id=$products->id;
				$cnt= count($request->att_id);
				$radio='$request->radio_';
				$dropdown='$request->dropdown_';
				$textbox='$request->textbox_';
			    $checkbox='$request->checkbox_';
					
				$i=1;
				$cntr='';
				$cntd='';
				$cntc='';
				$cntt='';
				$r='radio_';
				
			foreach($request->att_id as $a_id)
			{	$r='radio_';
			 $rvalue=$r.$i;
			
			 $cntr=count($request->$rvalue);				
				if($cntr != 0)
				{
					$rval=$request->$rvalue;	
					
					$MerchantProdAttributes = new MerchantProdAttributes;
					$MerchantProdAttributes->avalues=$rval;
					$MerchantProdAttributes->p_id=$id;
					$MerchantProdAttributes->a_id=$a_id;								
					$MerchantProdAttributes->save();									
					
				}
				$d='dropdown_';
				$dvalue=$d.$i;
				$cntd=count($request->$dvalue);
				if($cntd != 0)
				{
					$dval=$request->$dvalue;	
					$MerchantProdAttributes = new MerchantProdAttributes;
					$MerchantProdAttributes->avalues=$dval;
					$MerchantProdAttributes->p_id=$id;
					$MerchantProdAttributes->a_id=$a_id;								
					$MerchantProdAttributes->save();
					
	

				}
				$c='checkbox_';
				$cvalue=$c.$i;
				 $cntc=count($request->$cvalue);
				if($cntc != 0)
				{
					
					$cval=$request->$cvalue;				
					foreach($cval as $ch){	
					$MerchantProdAttributes = new MerchantProdAttributes;
					$MerchantProdAttributes->avalues=$ch;
					$MerchantProdAttributes->p_id=$id;
					$MerchantProdAttributes->a_id=$a_id;								
					$MerchantProdAttributes->save();
				
						}
				}
				$t='textbox_';
				$tvalue=$t.$i;
				$cntt=count($request->$tvalue);
				if($cntt != 0)
				{
					$tval=$request->$tvalue;	
					$MerchantProdAttributes = new MerchantProdAttributes;
					$MerchantProdAttributes->avalues=$tval;
					$MerchantProdAttributes->p_id=$id;
					$MerchantProdAttributes->a_id=$a_id;								
					$MerchantProdAttributes->save();					

				}
			$i++;
			}

			return redirect('artist/artworks/add/'.$request->cat_id)->with('message','Artwork added successfully');*/
	}
	public function artworks_view($id)
	{
		
		//$products= products::all();
			$user_id = Auth::user()->id;
			$products = DB::table('products')
				->leftJoin('category', 'category.c_id', '=', 'products.m_c_id')
				->where('products.merchant_id', '=', $user_id)
				->where('products.m_c_id', '=', $id)				
				->get();
		
		
		$generalusertype= GeneralUserType::all();
		$attributes= Attributes::all();
	   $language= MultiLanguage::all();
	   $country= Country::all();
	   //$category= Category::all();
	   $category = DB::table('category')
			 ->where('parent_id', '0')	 
			 ->get();
			 
	   $user_id = Auth::user()->id;
        $users = DB::table('users')
		->where('id', '=' , $user_id)
		->first();
		
	   $users = DB::table('users')
			->select('*', 'users.id as uid')
			->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
			->where('role_user.role_id', '=', 3)
			->get();
	   $subcategory = DB::table('category')
			 ->where('parent_id','!=', '0')
			 ->get();
		$editstates = DB::table('states')			 
			 ->get();
		$editcities = DB::table('cities')			 
			 ->get();
	    $cat = DB::table('category')
			->where('c_id', '=' , $id)
			->first(); 
		$title=$cat->c_title;
				  
	  return view('panels.artist.artworks_view',['id'=>$id,'title'=>$title,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
	}
	

	
	public function artworks_edit($cid,$id)
	{
		
		//$products= products::first()->where('id',$id);		
		
		$products = DB::table('products')			
				->where('products.id', '=', $id)
				->first();
				
		$generalusertype= GeneralUserType::all();
		$attributes= Attributes::all();
	   $language= MultiLanguage::all();
	   $country= Country::all();
	  // $category= Category::all();
	    $category = DB::table('category')
			 ->where('parent_id', '0')	 
			 ->get(); 
	  
			 
	   $user_id = Auth::user()->id;
        $users = DB::table('users')
		->where('id', '=' , $user_id)
		->first();
		
	   $users = DB::table('users')
			->select('*', 'users.id as uid')
			->leftJoin('role_user', 'users.id', '=', 'role_user.user_id')
			->where('role_user.role_id', '=', 3)
			->get();
	   $subcategory = DB::table('category')
			 ->where('parent_id','!=', '0')
			 ->get();
		$editstates = DB::table('states')			 
			 ->get();
		$editcities = DB::table('cities')			 
			 ->get();
	   
	  
	  
	  
	  $cat = DB::table('category')
			->where('c_id', '=' , $cid)
			->first(); 
$c_title=$cat->c_title;
$pcategory = DB::table('category')
			->where('parent_id', '=' , $cid)			 
			 ->get(); 			
			  if($cat->c_title=='Audio'){
				  //Audio Providers
$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
        return view('panels.artist.artworks_edit_audio',['id'=>$cid,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title' => $c_title]);
			 }elseif($cat->c_title=='Paintings'){
				  //Painters
$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
        return view('panels.artist.artworks_edit_painters',['id'=>$cid,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title' => $c_title]);
				  
			  }
			  elseif($cat->c_title=='Photography'){
				  
				  //Photographers
$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
       return view('panels.artist.artworks_edit_photographers',['id'=>$cid,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title' => $c_title]);
				  
			  }
			elseif($cat->c_title=='Videos'){
				  
				 //Video Providers
$scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
        return view('panels.artist.artworks_edit_video',['id'=>$cid,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title' => $c_title]);
			  }
			  else{
				  //Digital Artists
				  $scategory = DB::table('category')
			 ->where('parent_id','=', $cat->c_id)			
			 ->get();
       return view('panels.artist.artworks_edit_digital_art',['id'=>$cid,'pcategory'=>$pcategory,'scategory'=>$scategory,'products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities,'title' => $c_title]); 
				  
			  }
	  
	  
	  
	}
	
	public function artworkupdated(Request $request)
	{
		 $this->validate($request,
			 [			
			's_c_id'		=> 'required',		
			'title'			=> 'required',
			'description'	=> 'required',		
               
            ],
            [
			's_c_id.required'		=> 'Category is required',			
			'title.required'			=> 'Title is required',					
			'description.required'	=> 'required',
                     ]);
			
				$photo = $request->file('photo');
			 if($photo){
					$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
						  DB::table('products')
						->where('id', $request->id)
						->update(['image' => $imagename,]);
			 }		
				
                $id=$request->id;
				DB::table('products')
				->where('id', $request->id)
				->update([
				's_c_id'=>$request->s_c_id,				
				'name'=>$request->title,				
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view/'.$request->cat_id)->with('message',$request->category_title.' updated successfully');
/*		
	$this->validate($request,
			 [
			 
		//'merchant_id'		=> 'required',			
			'category'		=> 'required',
			'scategory'		=> 'required',
			'title'			=> 'required',			
			'photo'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg',	
			'description'	=> 'required',		
               
            ],
            [
			//'merchant_id.required'		=> 'Username is required',			
			'category.required'		=> 'Category is required',
			'scategory.required'		=> 'Subcategory is required',
			'title.required'			=> 'Title is required',			
			'photo.required'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg',
			'photo.image'           	=> 'Image should be a jpeg,png,gif,svg', 			
			'description.required'	=> 'required',
                     ]);
			$photo = $request->file('photo');
			 if($photo){
					$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->fit(214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);
				
				$destinationPathbig = public_path('/uploads/products/bigthumbnail');
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
						  DB::table('products')
						->where('id', $request->id)
						->update(['image' => $imagename,]);
			 }
			$id=$request->id;
				DB::table('products')
				->where('id', $request->id)
				->update([
				'merchant_id'=>$request->merchant_id,			
				'c_type'=>$request->c_type,
				'm_c_id'=>$request->category,
				's_c_id'=>$request->scategory,				
				'price'=>$request->price,						
				'name'=>$request->title,
				'meta_tag'=>$request->meta_tag,
				'meta_description'=>$request->meta_description,
				'description'=>$request->description,			
				'discount_type'=>$request->discount_type,
				'discount_group'=>$request->group,
				'discount_amount'=>$request->amount,
				'discount_percentage_amt'=>$request->d_type,
				'discount_from_date'=>$request->from_date,
				'discount_to_date'=>$request->end_date,
				'discount_status'=>$request->status,							
				]);

			//Update attributes	
			$cnt= count($request->att_id);
				$radio='$request->radio_';
				$dropdown='$request->dropdown_';
				$textbox='$request->textbox_';
			    $checkbox='$request->checkbox_';
					
				$i=1;
				$cntr='';
				$cntd='';
				$cntc='';
				$cntt='';
				$r='radio_';
				
			foreach($request->att_id as $a_id)
			{	$r='radio_';
			 $rvalue=$r.$i;
			
			 $cntr=count($request->$rvalue);				
				if($cntr != 0)
				{
					 $rval=$request->$rvalue;
					 $dbrval=DB::table('merchant_prod_attributes')
					->where('id', $a_id)->first();
					
					if($dbrval->id != ''){
						
						 DB::table('merchant_prod_attributes')
						->where('id', $a_id)
						->update(['avalues'=>$rval]);
					
					}else{	
						
						$MerchantProdAttributes = new MerchantProdAttributes;
						$MerchantProdAttributes->avalues=$rval;
						$MerchantProdAttributes->p_id=$id;
						$MerchantProdAttributes->a_id=$a_id;								
						$MerchantProdAttributes->save();
					
					}
					 							
					
				}
				$d='dropdown_';
				$dvalue=$d.$i;
				$cntd=count($request->$dvalue);
				if($cntd != 0)
				{
					 $dval=$request->$dvalue;
					  $dbdval=DB::table('merchant_prod_attributes')
					  ->where('id', $a_id)->first();
					
					if($dbdval->id != ''){
						
						 DB::table('merchant_prod_attributes')
						->where('id', $a_id)
						->update(['avalues'=>$dval]);
					
					}else{	
						
						$MerchantProdAttributes = new MerchantProdAttributes;
						$MerchantProdAttributes->avalues=$dval;
						$MerchantProdAttributes->p_id=$id;
						$MerchantProdAttributes->a_id=$a_id;								
						$MerchantProdAttributes->save();
					
					}
				
				}
				$c='checkbox_';
				$cvalue=$c.$i;
				 $cntc=count($request->$cvalue);
				if($cntc != 0)
				{
					
					$cval=$request->$cvalue;				
					foreach($cval as $ch){					
					
					 $dbdval=DB::table('merchant_prod_attributes')
					  ->where('id', $a_id)->first();
					
					if($dbdval->id != ''){
						
						 DB::table('merchant_prod_attributes')
						->where('id', $a_id)
						->update(['avalues'=>$ch]);
					
					}else{	
						
						$MerchantProdAttributes = new MerchantProdAttributes;
						$MerchantProdAttributes->avalues=$ch;
						$MerchantProdAttributes->p_id=$id;
						$MerchantProdAttributes->a_id=$a_id;								
						$MerchantProdAttributes->save();
					
					}
				
						}
				}
				$t='textbox_';
				$tvalue=$t.$i;
				$cntt=count($request->$tvalue);
				if($cntt != 0)
				{
					$tval=$request->$tvalue;	
					DB::table('merchant_prod_attributes')
					->where('id', $a_id)
					->update(['avalues'=>$tval]);				

				}
			$i++;
			}

				
	return redirect('artist/artworks/edit/'.$request->cat_id)->with('message','Artwork updated successfully');*/
	
	}
	
	
	public function deleted(Request $request)
	{	 
	$id=$request->id;  
	
		 $user = DB::table('users')
		 ->where('id', $id)
		 ->delete();
		 $prd = DB::table('products')
		 ->where('merchant_id', $id)
		 ->delete();
		 $status['deletedid']=$id;
		 $status['deletedtatus']='User deleted successfully';
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
			DB::table('products')
			 ->where('merchant_id', $id)
			 ->delete();				
				}			
			} 
	return redirect('admin/users')->with('message','Seltected Users are deleted successfully');			

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