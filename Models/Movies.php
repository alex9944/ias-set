<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Movies extends Model
{
     protected $table = 'movies';
	 protected $primaryKey='id';
	 public function categories()
    {
        return $this->belongsTo('App\Models\MoviesType','m_t_id');
    }
}
