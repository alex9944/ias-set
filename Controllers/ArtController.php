<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Banner;
use App\Models\Category;
use DB;
use Session;
class ArtController extends Controller
{

	public function index($slug=null)
    {
		$menu = DB::table('menu')
				 ->where('parent_id', 0)
				 ->orderby('order_by', 'asc')		 
				 ->get();
		if($slug)
		{
			$cat = DB::table('category')
				 ->where('c_slug_id', $slug)					 
				 ->first();	

            $products = DB::table('products')
            ->select('*', 'products.id as pid','products.image as pimage')	
			->leftJoin('users', 'users.id', 'products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id', 'users.user_type_sub')			
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->where('products.m_c_id', $cat->c_id)
			 ->groupBy('products.id')
			->get();		
			
		}else{
			$cat='';
			$products = DB::table('products')
            ->select('*', 'products.id as pid','products.image as pimage')			
			->leftJoin('users', 'users.id', 'products.merchant_id')			
			->leftJoin('merchants_type', 'merchants_type.id', 'users.user_type_sub')					
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			 ->groupBy('products.id')
			->get();
			
			$slug='';
		}
		/*$category = DB::table('category')			
			->leftJoin('category_slug', 'category_slug.id', '=', 'category.c_slug_id')
			->where('parent_id', '0')			
			->get();*/
		$category = Category::where('parent_id', '0')	 
			 ->get();
        return view('pages.art',['menu'=>$menu,'products'=>$products,'category'=>$category,'cat'=>$cat,'slug'=>$slug]);

    }
	public static function get_art_sub_menu($id)
	{		
    $cat = DB::table('category')
				 ->where('parent_id', $id)
				 ->orderby('c_id', 'asc')				 
				 ->get();
	             		
			
				$tit=''; 
				foreach($cat as $cat)
				 {	             
                    $tit .= '<li><a  href="'.url('/').'/'.$cat->c_slug_id.'">'.$cat->c_title.'</a></li>';
			 }
			echo $tit;
		
	}

		public function onload_products($page,$sort,$slug=null)
	{	

	//$page = $_GET['page'];
	$start = ($page - 1)*12;    
   // $sort = $_GET['sort'];	
  // 
  
  if($slug!=null)
		{
			$cat = DB::table('category')
				 ->where('c_slug_id', $slug)					 
				 ->first();	


			$products = DB::table('products')
			->select('*', 'products.id as pid','products.image as pimage')
			->leftJoin('users', 'users.id', 'products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id', 'users.user_type_sub')
			->where('products.status', 'Enable')			
			->where('m_c_id', $cat->c_id)
			->orWhere('s_c_id', $cat->c_id)
			->where('users.activated', '1')
			->orderBy('products.id', 'desc')
			->offset($start)->limit(12)			
            ->get();
				
			
		}else{
			
			$products = DB::table('products')
			->select('*', 'products.id as pid','products.image as pimage')
			->leftJoin('users', 'users.id', 'products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id', 'users.user_type_sub')			
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->orderBy('products.id', 'desc')
			->offset($start)->limit(12)	
            ->get();			
		} 
		 
	 echo '{"products": ' . json_encode($products) . '}';
		
	}
	
	public function searchkeyword_products($keyw)
	{	

		
			$products = DB::table('products')
			->select('*', 'products.id as pid','products.image as pimage')
			->leftJoin('users', 'users.id', 'products.merchant_id')
			->leftJoin('merchants_type', 'merchants_type.id', 'users.user_type_sub')		
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->Where('products.name', 'like', '%'.$keyw.'%')
			->orderBy('products.id', 'desc')			
            ->get();	
	
	  echo '{"products": ' . json_encode($products) . '}';
		
	}
	
	/*
	public function products($page=null,$sort=null)
	{
	
		
		$this->_loadModel('Products_front_model');
		
		$this->data['category_extension'] = $this->_getCategory();
		
		// get site settings
		$site_settings = $this->_getSiteSettings();
		
		// get template
		$current_template = $this->db->get_where('templates', array('id' => $site_settings->template_id), 1)->row();
		
		$this->data['user_template'] = $current_template->slug;
		
		$brand = '';
		if(stripos($search, 'brand_') !== false) {
			$brand = str_ireplace('brand_', '', $search);
			$search = '';
		}
		
		// filter model
		$filter_value_ids = $this->input->post('filter_value_ids');
		$filter_price_range = $this->input->post('filter_price_range');
		
		$filter_data = array('category_id' => $category_id, 'subcategory_id' => $subcategory_id, 'search' => $search, 'brand' => $brand, 'filter_value_ids' => $filter_value_ids, 'filter_price_range' => $filter_price_range);
		
		$this->data['item_data'] = $this->Products_front_model->get_productlist_item_data($page,$sort,$filter_data);
		$product_layout = $this->_getSingleProductLayout($this->data['user_template']);
		
		echo '{"products": ' . json_encode($this->data['item_data']) . ', "product_layout":' . json_encode($product_layout) . '}';	
	}
	
	*/
	
					
	public function art_details($id)
    {
		$menu = DB::table('menu')
				 ->where('parent_id', 0)
				 ->orderby('order_by', 'asc')		 
				 ->get();
				 
			$details = DB::table('merchant_prod_attributes as mpa')		
			->leftJoin('products_attributes as pa', 'pa.id', 'mpa.a_id')
			->leftJoin('products_attributes_values as pav', 'pav.av_id','mpa.avalues')
			->where('mpa.p_id', $id)
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->orderBy('products.id', 'desc')
			->get();
			
			$products = DB::table('users')
			->select('*', 'users.id as uid')
			->leftJoin('products', 'products.merchant_id', '=', 'users.id')
			->where('products.id', $id)
			->where('products.status', 'Enable')
			->where('users.activated', '1')
			->orderBy('products.id', 'desc')
			->first();
			
		
		//$products= products::all()->where('status', 'Enable')->where('id', $id)->first();
		$similar= products::all()->where('status', 'Enable');
		
		
        return view('pages.art_details',['menu'=>$menu,'products'=>$products,'similar'=>$similar,'details'=>$details,'products'=>$products,'id'=>$id,]);

    }
	
	
	
	public static function get_menu()
	{			
			$menu = DB::table('menu')
				 ->where('parent_id', 0)
				 ->orderby('order_by', 'asc')		 
				 ->get();
				$tit=''; 
				foreach($menu as $menu)
				 {	             
                    $tit .= '<li><a  href="'.url('/').'/'.$menu->slug.'">'.$menu->title.'</a></li>';
			 }
			echo $tit;
		
	}
	 public function DynamicPages($slug = null)
    {

		 $page = DB::table('menu')
			//->select('*', 'users.id as uid')
			->leftJoin('pages', 'pages.menu_id', '=', 'menu.id')
			->where('menu.slug', '=', $slug)
			->first();
		
		$menu = DB::table('menu')
				 ->where('parent_id', 0)
				 ->orderby('order_by', 'asc')		 
				 ->get();
		$products= products::all()->where('status', 'Enable');		 
        return view('pages.dynamicpage',['menu'=>$menu,'page'=>$page,'products'=>$products,]);

    }
	 public function getContact()
    {
       $menu = DB::table('menu')
				 ->where('parent_id', 0)
				 ->orderby('order_by', 'asc')		 
				->get();
        return view('pages.contact_us',['menu'=>$menu,]);

    }
	
	
	 public function ContactStore(Request $request)
{

    Mail::send('emails.contact',
        array(
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'user_message' => $request->get('message')
        ), function($message)
    {
        $message->from('alex@wjgilmore.com');
        $message->to('alexander.mca08@gmail.com', 'Admin')->subject('Needifo Feedback');
    });

  return Redirect::route('contact-us')->with('message', 'Thanks for contacting us!');

}
	
	
	
}