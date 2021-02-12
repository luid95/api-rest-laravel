<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'category_id',
        'image'
    ];
    
    //Indicamos que este modelo va a utilizar una tabla de nuestra base de datos
    protected $table = 'posts';

    //Relacion de 1 a * inversa; es decir de * a. Con respecto a la tabla de posts.
    public function user() {
        return $this->belongsTo('App\Models\User', 'user_id');
    } 

    public function category(){
        return $this->belongsTo('App\Models\Category', 'category_id');
    }
}
