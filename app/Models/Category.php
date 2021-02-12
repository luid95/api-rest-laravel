<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //Indicamos que este modelo va a utilizar una tabla de nuestra base de datos
    protected $table = 'categories';

    //Relacion de 1 a *. Con respecto a la tabla de posts.
    public function posts() {
        return $this->hasMany('App\Models\Post');
    }
}
