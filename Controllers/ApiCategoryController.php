<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Models\Category;
class ApiCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $category=Category::all()
	  ->where('parent_id','0');
	  $data['category']=$category;
		 foreach ($category as $category){

						array_push($data, $category->subcategories);
									foreach ($category->subcategories as $subcategory)
									{
										array_push($data, $subcategory->subcategories); 	
									}
		 } 
      $responsecode = 200;        
      $header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
            );       
   return response()->json($data , $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	

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
		 $category=Category::all()->where('c_id',$id)->first(); 
		 $data['category'][]=$category;		
		 $responsecode = 200;        
			$header = array (
                'Content-Type' => 'application/json; charset=UTF-8',
                'charset' => 'utf-8'
				 );  
   return response()->json($data, $responsecode, $header, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);	
    }
	public function subcategory($id)
		{
			
			$subcategory =DB::table('category')->where('parent_id',$id)->get();
			//dd(DB::getQueryLog($duration));			
            $data['subcategory']=$subcategory;			
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
