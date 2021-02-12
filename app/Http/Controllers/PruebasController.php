<?php

namespace App\Http\Controllers;
//use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Category;

class PruebasController extends Controller
{
    //
    public function index() {

        $titulo = 'Animales';

        $animales = [
            'Perro',
            'Gato',
            'Tigre'
        ];

        return view('pruebas.index', array(
            'titulo' => $titulo,
            'animales' => $animales
        ));
    }

    public function testOrm(){

        // Es como si si se usara el  SELECT * FROM Post ; 
        /*$sql = 'SELECT * FROM posts, categories, users WHERE posts.user_id=users.id AND posts.category_id=categories.id';
        $posts = DB::select($sql);

        foreach ($posts as $post){
            echo "<h1>" .$post->title. "</h1>";
            //si queremos saber el nombre de usuario
            echo "<span style='color:gray;' > {$post->name_u} - {$post->name_c} </span>";
            echo "<p>" .$post->content. "</p>";
            echo "<hr>";
        }
        /////////////////////////////////////////////////////////
        $sql = 'SELECT * FROM categories';
        $categories = DB::select($sql);
        
        foreach ($categories as $category){
            echo "<hr>";
            echo "<h1><sapn>{$category->id} -- </span>{$category->name_c}</h1>";

            $sql2 = 'SELECT posts.id as p_id, categories.id as c_id, users.id as u_id, categories.name_c, users.name_u, posts.title, posts.content
                    FROM posts
                    JOIN categories ON categories.id = posts.category_id
                    JOIN users ON users.id = posts.user_id
                    WHERE posts.category_id=?';
            $posts = DB::select($sql2,
                                    [
                                        $category->id
                                    ]
                                );
            
            foreach ($posts as $post){

                echo "<h3><sapn>{$post->p_id} - </span>" .$post->title. "</h3>";
                //si queremos saber el nombre de usuario
                echo "<span style='color:gray;' >{$post->name_u} - {$post->name_c}</span>";
                echo "<p>" .$post->content. "</p>";
                
            }
            echo "<hr>";
            
        }*/

        /*$posts = Post::all();
        
        foreach ($posts as $post){
            echo "<h1>" .$post->title. "</h1>";
            //si queremos saber el nombre de usuario
            echo "<span style='color:gray;' > {$post->user->name_u} - {$post->category->name_c} </span>";
            echo "<p>" .$post->content. "</p>";
            echo "<hr>";
        }*/

        $categories = Category::all();
        
        foreach ($categories as $category){
            echo "<hr>";
            echo "<h1><sapn>{$category->id} -- </span>{$category->name_c}</h1>";

            foreach ($category->posts as $post){

                echo "<h3><sapn>{$post->id} - </span>" .$post->title. "</h3>";
                //si queremos saber el nombre de usuario
                echo "<span style='color:gray;' >{$post->user->name} - {$post->category->name}</span>";
                echo "<p>" .$post->content. "</p>";
                
            }
            echo "<hr>";
            
        }

        die();

    }
}
