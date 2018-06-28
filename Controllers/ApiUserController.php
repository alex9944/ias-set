<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use App\Models\SubscriptionFeatures;
use App\Models\Role;
use App\Models\UserAccessModules;
use App\Traits\ActivationTrait;
use App\Http\Controllers\Controller;
use Image;
use Session;
use DB;
use Illuminate\Support\Facades\Auth;

class ApiUserController extends Controller {

   
	 public function library($id)
    {	
       $user_library = DB::table('user_library')
			->select('*', 'products.id as pid')				
			->leftJoin('category', 'category.c_id', '=', 'user_library.category_id')
			->leftJoin('products', 'products.id', '=', 'user_library.product_id')			
			->where('user_library.user_id','=',$id)
			->get();
			$responsecode = 200;  

		
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($user_library , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	
	
	
	
	
}