<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Post;

use App\Helpers\JwtAuth;

class PostController extends Controller
{
    public function __construct()
    {
        // Indicar en que metodos no aplicar el middleware
        $this->middleware('api.auth', ['except' => [
                        'index', 
                        'show', 
                        'getImage',
                        'getPostsByCategory',
                        'getPostsByUser'
        ]]);
    }

    // Obtener todas los post que hay en  nuestra base de datos
    public function index()
    {

        $posts = Post::all()->load('category');

        return response()->json([
            'code'          => 200,
            'status'        => 'success',
            'posts'    => $posts
        ], 200);
    }

    // Obtener una categoria
    public function show($id)
    {

        $post = Post::find($id)->load('category')
                                ->load('user');

        if (!empty($post) && is_object($post)) {

            $data = [
                'code'          => 200,
                'status'        => 'success',
                'category'    => $post
            ];
        } else {

            $data = [
                'code'          => 404,
                'status'        => 'error',
                'message'    => 'La entrada no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    // Guardar un post
    public function store(Request $request)
    {
        // Recoger los datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {
            // Conseguir usuario identificado
            $user = $this->getIdentity($request);

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'title'       => 'required',
                'content'     => 'required',
                'category_id' => 'required',
                'image'       => 'required'
            ]);

            if ($validate->fails()) {

                $data = [
                    'code'          => 404,
                    'status'        => 'error',
                    'message'       => 'No se ha guardado el post, faltan datos.'
                ];
            } else {

                // Guardar el articulo
                $post = new Post();
                $post->user_id = $user->sub;
                $post->category_id = $params->category_id;
                $post->title = $params->title;
                $post->content = $params->content;
                $post->image = $params->image;
                $post->save();

                $data = [
                    'code'          => 200,
                    'status'        => 'success',
                    'post'       => $post
                ];
            }
        } else {

            $data = [
                'code'          => 404,
                'status'        => 'error',
                'message'       => 'Envia los datos correctamente.'
            ];
        }

        // Devolver el resultado
        return response()->json($data, $data['code']);
    }

    // Actualizar una categoria
    public function update($id, Request $request)
    {
        // Recoger los datos de POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        // Datos para devolver
        $data = [
            'code'       => 404,
            'status'     => 'error',
            'message'    => 'Datos enviados incorrectamente.'
        ];

        if (!empty($params_array)) {

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'title' => 'required',
                'content' => 'required',
                'category_id' => 'required',
            ]);

            if ($validate->fails()) {
                $data['errors'] = $validate->errors();

                return response()->json($data, $data['code']);
            }

            // Eliminar lo que no queremos actualizar
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);

            // Conseguir usuario identificado
            $user = $this->getIdentity($request);

            // Buscar al registro a actualizar
            $post = Post::where('id', $id)
                ->where('user_id', $user->sub)
                ->first();

            if (!empty($post) && is_object($post)) {

                // Actualizar el registro en concreto
                $post->update($params_array);

                $data = [
                    'code'      => 200,
                    'status'    => 'success',
                    'post'      => $post,
                    'change'    => $params_array
                ];
            }
        }

        // Devolver los datos
        return response()->json($data, $data['code']);
    }

    // Eliminar un post
    public function destroy($id, Request $request)
    {
        // Conseguir usuario identificado
        $user = $this->getIdentity($request);

        // Conseguir el registro
        $post = Post::where('id', $id)
            ->where('user_id', $user->sub)
            ->first();

        if (!empty($post)) {

            // Borrarlo
            $post->delete();


            $data = [
                'code'       => 200,
                'status'     => 'success',
                'post'    => $post
            ];
        } else {
            $data = [
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'El post no existe'
            ];
        }

        // Devolver los datos
        return response()->json($data, $data['code']);
    }

    private function getIdentity($request)
    {

        // Conseguir usuario identificado
        $jwtAuth = new JwtAuth();
        $token = request()->header('Authorization', null);
        $user = $jwtAuth->checkToken($token, true);

        return $user;
    }

    public function upload(Request $request){

        // Recoger la imagen de la peticion
        $image = $request->file('file0');

        // Validar la imagen
        $validate = \Validator::make($request->all(), [
            'file0'     =>'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        // Guardar la imagen
        if(!$image || $validate->fails()){

            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error al subir imagen.'
            );

        }else{

            $image_name = time().$image->getClientOriginalName();

            // Para guardar mis imagene en mi carpeta de images dentro de mi carpeta storage
            \Storage::disk('images')->put($image_name, \File::get($image)); 

            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'image'     => $image_name
            );
        }

        // Devolver datos
        return response($data, $data['code']);
    }

    public function getImage($filename){

        // Comprobar si existe el fichero
        $isset = \Storage::disk('images')->exists($filename);
        
        if($isset){
            // Conseguir la imagen
            $file = \Storage::disk('images')->get($filename);

            // Devolver la imagen
            return new Response($file, 200);
        }else{

            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'     => 'La imagen no existe'
            );
        }

        // Mostrar error
        return response()->json($data, $data['code']);
    }

    // Listar post por usuario y categoria
    public function getPostsByCategory($id){
        $posts = Post::where('category_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'posts'  => $posts
        ], 200);
    }
    
    public function getPostsByUser($id){
        $posts = Post::where('user_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'posts'  => $posts
        ], 200);
    }
}
