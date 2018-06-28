<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Category;
use DB;
class ApiProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    	
	/*
	  $data['products'] = DB::table('products')
			->select('id','merchant_id','category.c_type','m_c_id','name','description','image','audio_url','video_url','c_title')	  
			->leftJoin('category', 'category.c_id', '=', 'products.m_c_id')			
			->get();*/
			
			
			$data['products']= DB::table('products')
			->select('products.id','category.c_title as main_cat','category.c_id','products.merchant_id','products.c_type','products.m_c_id','products.s_c_id','products.duration',	'products.name','products.description','products.image','products.audio_url','products.video_url','c_title')
			->leftJoin('users', 'users.id', 'products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id', 'users.user_type_sub')
			->leftJoin('category', 'products.m_c_id', '=', 'category.c_id')
			/*->whereNotIn('products.id', function($q){	
			$q->select('user_library.product_id')->from('user_library');
			})*/
			->where('products.status', 'Enable')			
            ->get();
			foreach($data['products'] as $value){
				//print_r($value->c_id);
				$value->sub_cat=DB::table('category')->where('parent_id',$value->c_id)->value('c_title');
			}
			
	
	 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	  //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
	//$products= Products::all()->where('id',$id)->first();
	//$data['products'][]=$products;	
	
	  $data['products'] = DB::table('products')
			->select('id','merchant_id','products.m_c_id','products.name','products.description','products.image','products.audio_url','products.video_url','products.duration','category.c_title')
			->leftJoin('category', 'category.c_id', '=', 'products.m_c_id')
			->where('products.id', '=', $id)
			->get();
			
    $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
    return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
	
	 public function category($id)
    {
	$products= Products::all()->where('id',$id)->first();
	$data['products'][]=$products;
    $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
    return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
