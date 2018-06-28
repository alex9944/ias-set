<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MoviesType extends Model
{
     protected $table = 'movies_type';
	 protected $primaryKey='id';
	  public function movies()
    {
		return $this->hasMany('App\Models\Movies', 'm_t_id');
       
    }
	
}
