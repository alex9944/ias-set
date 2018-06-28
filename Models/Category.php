<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
     protected $table = 'category';
	 protected $primaryKey='c_id';
	 public function subcategories()
    {
        return $this->hasMany('App\Models\Category', 'parent_id', 'c_id');
    }
}
