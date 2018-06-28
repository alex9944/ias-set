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
        return view('panels.artist.home');
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
			 
        return view('panels.artist.profile',['users'=>$users,'Country'=>$Country,'editstates' => $editstates,'editcities' => $editcities]);
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
		
        return view('panels.artist.password',['users'=>$users]);
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
        return view('panels.admin.users', ['users' => $users]);
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
				 if($photo->getClientOriginalExtension()=='jpg' || $photo->getClientOriginalExtension()=='png' || $photo->getClientOriginalExtension()=='jpeg')
				 {
					$imagename = time().'.'.$photo->getClientOriginalExtension();   
					$destinationPath = public_path('uploads\thumbnail');
					$thumb_img = Image::make($photo->getRealPath())->resize(100, 100);
					$thumb_img->save($destinationPath.'/'.$imagename,80);                    
					$destinationPath = public_path('uploads\original');
					$photo->move($destinationPath, $imagename);
						 DB::table('users')
						->where('id', $request->id)
						->update(['image' => $imagename,]);
				 }else{
					return redirect('artist/profile')->with('message','Your Profile image is Invalid file format'); 
				 }
						
			 }
			 $banner = $request->file('banner');
			  if($banner){
				   if($photo->getClientOriginalExtension()=='jpg' || $photo->getClientOriginalExtension()=='png' || $photo->getClientOriginalExtension()=='jpeg')
				 {
					$bannerimg = time().'.'.$banner->getClientOriginalExtension();   
					$destinationPath = public_path('uploads\banner\thumbnail');
					$thumb_img = Image::make($banner->getRealPath())->resize(100, 100);
					$thumb_img->save($destinationPath.'/'.$bannerimg,80);                    
					$destinationPath = public_path('uploads\banner\original');
					$banner->move($destinationPath, $bannerimg);
						 DB::table('users')
						->where('id', $request->id)
						->update(['banner' => $bannerimg,]);
						}else{
					return redirect('artist/profile')->with('message','Your Banner image is Invalid file format'); 
				 }
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

public function artworks_add()
    {	
	 
	   $products= products::all();
	   $attributes= Attributes::all();
	   $language= MultiLanguage::all();
	   $country= Country::all();
	   $generalusertype= GeneralUserType::all();
	   
	   //$category= Category::all();
	  
			
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
			  
			$user_type= Auth::user()->user_type; 
			
			  if($user_type==1){
				  //Audio Providers
			$category = DB::table('category')
			 ->where('parent_id', '0')	 
			 ->get();
        return view('panels.artist.artworks_audio',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
			  }elseif($user_type==2){
				  //Painters
			$category = DB::table('category')
			 ->where('parent_id', '30')	 
			 ->get();
        return view('panels.artist.artworks_painters',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);  
				  
			  }
			  elseif($user_type==3){
				  
				  //Photographers
			$category = DB::table('category')
			 ->where('parent_id', '0')	 
			 ->get();
        return view('panels.artist.artworks_photographers',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
				  
			  }
			  elseif($user_type==4){
				  
				 //Video Providers
			$category = DB::table('category')
			 ->where('parent_id', '0')	 
			 ->get();

        return view('panels.artist.artworks_video',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]); 
			  }
			  else{
				  //Digital Artists
			$category = DB::table('category')
			 ->where('parent_id', '34')	 
			 ->get();
        return view('panels.artist.artworks_digital_art',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);  
				  
			  }
			
    }
	
	
	public function digital_arts_added(Request $request)
	{
		
		
		
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
				//$thumb_img = Image::make($photo->getRealPath())->crop(543, 543);
				$thumb_img = Image::make($photo->getRealPath())->fit(543, 343, function ($constraint) 
				{
						$constraint->upsize();
						});
				$thumb_img->save($destinationPathbig.'/'.$imagename,80);
				
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);
			/*
				$photo = $request->file('photo');
				$imagename = time().'.'.$photo->getClientOriginalExtension();   
				$destinationPath = public_path('/uploads/products/thumbnail');
				$thumb_img = Image::make($photo->getRealPath())->crop(214, 214);
				$thumb_img->save($destinationPath.'/'.$imagename,80);                    
				$destinationPath = public_path('/uploads/products/original');
				$photo->move($destinationPath, $imagename);		
				*/
                $user_id = Auth::user()->id;             
				$products = new products;				
				$products->merchant_id=$user_id;			
				
				$products->m_c_id=$request->category;									
				$products->name=$request->title;							
				$products->image=$imagename;				
				$products->description=$request->description;				
				$products->save();
				
				$id=$products->id;	
		

			return redirect('artist/artworks/add')->with('message','Digital Art added successfully');
	}
	public function digital_arts_edit(Request $request)
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
			
				$photo = $request->file('photo');
			 if($photo){
					/* $imagename = time().'.'.$photo->getClientOriginalExtension();   
					$destinationPath = public_path('/uploads/products/thumbnail');
					$thumb_img = Image::make($photo->getRealPath())->crop(100, 100);
					$thumb_img->save($destinationPath.'/'.$imagename,80);                    
					$destinationPath = public_path('/uploads/products/original');
					$photo->move($destinationPath, $imagename);
						  DB::table('products')
						->where('id', $request->id)
						->update(['image' => $imagename,]);*/
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
				'name'=>$request->title,				
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view')->with('message','Digital Art updated successfully');
	}
	
	
	//Photographers
	
	public function photographers_added(Request $request)
	{
		
		
		
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
				$products->name=$request->title;							
				$products->image=$imagename;				
				$products->description=$request->description;				
				$products->save();
				
				$id=$products->id;	
		

			return redirect('artist/artworks/add')->with('message','Photograph added successfully');
	}
	public function photographers_edit(Request $request)
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
				'name'=>$request->title,				
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view')->with('message','Photograph updated successfully');
	}
	
	////End Photographers
	
	
	// Audio 
	

	
	public function audio_added(Request $request)
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
				$products->name=$request->title;
				$products->audio_url=$img.'.mp3';				
				$products->image='audio.jpg';				
				$products->description=$request->description;				
				$products->save();
				
				$id=$products->id;	
		

			return redirect('artist/artworks/add')->with('message','Audio added successfully');
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
				->update([										
				'name'=>$request->title,
				'audio_url'=>$request->audio_url,				
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view')->with('message','Audio updated successfully');
	}
	
	//Video

	
	public function video_added(Request $request)
	{
		//mp4,x-flv,x-mpegURL,MP2T,3gpp,quicktime,x-msvideo,x-ms-wmv
		//'video_url'			=> 'required',	
		//'video_url.required'			=> 'Video url is required',	
		
		  $this->validate($request,
			 [
			 
				
				
			'title'			=> 'required',	
			'photo'			=> 'required',				
			//'photo'			=> 'required|mimes:mp4,avi,wmv,mov,3gp,flv,m3u8,ogx,oga,ogv,ogg,webm','description'	  => 'required',		
               
            ],
            [				
			'title.required'			=> 'Title is required',	
			'photo.required'           	=>  'required',			
		//	'photo.required'			=> 'Video should be a webm',
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
				$products->name=$request->title;
				$products->video_url=$img.'.webm';				
				$products->image=$image;				
				$products->description=$request->description;				
				$products->save();
				
				$id=$products->id;	
		

			return redirect('artist/artworks/add')->with('message','Video added successfully');
	}
	public function video_edit(Request $request)
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
				->update([										
				'name'=>$request->title,
				'description'=>$request->description,										
				]);		

			return redirect('artist/artworks/view')->with('message','Video updated successfully');
	}
	
	public function added(Request $request)
	{
		
		
		
		  $this->validate($request,
			 [
			 
				
			'scategory'		=> 'required',			
			'title'			=> 'required',			
			'photo'			=> 'required|image|mimes:jpeg,png,jpg,gif,svg',	
			'description'	=> 'required',		
               
            ],
            [		
			'scategory.required'		=> 'Category is required',			
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

			return redirect('artist/artworks/add')->with('message','Artwork added successfully');
	}
	public function artworks_view()
	{
		
		//$products= products::all();
			$user_id = Auth::user()->id;
			$products = DB::table('products')
				->leftJoin('category', 'category.c_id', '=', 'products.m_c_id')
				->where('products.merchant_id', '=', $user_id)
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
	   
	  return view('panels.artist.artworks_view',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
	}
	

	
	public function artworks_edit($id)
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
	   
	  
	  
	  
	  $user_type= Auth::user()->user_type; 
			  if($user_type==1){
				  //Audio Providers

        return view('panels.artist.artworks_edit_audio',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
			  }elseif($user_type==2){
				  //Painters

        return view('panels.artist.artworks_edit_painters',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
				  
			  }
			  elseif($user_type==3){
				  
				  //Photographers

       return view('panels.artist.artworks_edit_photographers',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
				  
			  }
			  elseif($user_type==4){
				  
				 //Video Providers

        return view('panels.artist.artworks_edit_video',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]);
			  }
			  else{
				  //Digital Artists
       return view('panels.artist.artworks_edit_digital_art',['products'=>$products,'generalusertype'=>$generalusertype,'editgeneralusertype'=>$generalusertype,'attributes'=>$attributes,'editattributes'=>$attributes,'prod'=>$products,'users' => $users,'language' => $language,'country' => $country,'category' => $category,'editusers' => $users,'editlanguage' => $language,'editcountry' => $country,'editcategory' => $category,'editsubcategory' => $subcategory,'editstates' => $editstates,'editcities' => $editcities]); 
				  
			  }
	  
	  
	  
	}
	
	public function updated_paint(Request $request)
	{
	
	$this->validate($request,
			 [
			 
		//'merchant_id'		=> 'required',			
		
			'scategory'		=> 'required',
			'title'			=> 'required',		
			'description'	=> 'required',		
               
            ],
            [
				
		
			'scategory.required'		=> 'Subcategory is required',
			'title.required'			=> 'Title is required',	
				
			'description.required'	=> 'required',
                     ]);
		
		
					
			$photo = $request->file('photo');
	
			 if($photo){
					$photo = $request->file('photo');
					if($photo->getClientOriginalExtension()=='jpg' || $photo->getClientOriginalExtension()=='png' || $photo->getClientOriginalExtension()=='jpeg')
				 {
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
				 }else{
					 
					return redirect('artist/artworks/edit/'.$request->id)->with('message','Artwork Image format is invalid'); 
				 }
			 }
			
			$id=$request->id;
				DB::table('products')
				->where('id', $request->id)
				->update([
				's_c_id'=>$request->scategory,							
				'name'=>$request->title,				
				'description'=>$request->description,							
				]);

		
				
		

				
	return redirect('artist/artworks/edit/'.$id)->with('message','Artwork updated successfully');
	
	}
	
	
	public function deleted(Request $request)
	{	 
	$id=$request->id;  
	
		 $blogs = DB::table('users')
		 ->where('id', $id)
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