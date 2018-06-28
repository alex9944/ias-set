<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\UserAccessModules;
use App\Http\Controllers\Controller;
use App\Models\FreeResources;
use Image;
use Session;
use DB;
use Illuminate\Support\Facades\Auth;

class ApiFreeImageController extends Controller {

   
	 public function index()
    {	
					
       
	   
	   $freeresources=freeresources::all();
	   $data['freeresources']=$freeresources;
			$responsecode = 200;        
             $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	
	
	
	
	
}