<?php

namespace App\Http\Controllers;

//use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

//importamos el modelo de usuario
use App\Models\User;

class UserController extends Controller
{
    //funcion de prueba
    public function pruebas(Request $request)
    {

        return "Accion de pruebas de UserController";
    }

    public function register(Request $request)
    {

        //Recogemos los datos por post
        $json = $request->input('json', null);

        $params = json_decode($json); //Obtener un objeto
        $params_array = json_decode($json, true); //array

        //Verificar si mi array no esta vacio
        if (!empty($params) && !empty($params_array)) {

            //Limpiar los datos
            $params_array = array_map('trim', $params_array);

            //Validar datos con la libreria "\Validator"
            $validate = \Validator::make($params_array, [
                'name_u' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users',
                'password' => 'required'
            ]);

            if ($validate->fails()) {

                //La validacion ha fallado
                $data = array(
                    'status'    => 'error',
                    'code'      => 404,
                    'message'   => 'El usuario no se ha creado',
                    'errors'    => $validate->errors()
                );
            } else {

                //Validacion pasada correctamente
                //Cifrar la contrasena
                $pwd = hash('sha256', $params->password);

                //Comprobar si el usuario existe (duplicado)
                //Realizandolo en el Validate agregando otra caracteristica al email
                //Especificando la table en la que se encuentra 'email' => 'unique:users'

                //Crear el usuario
                /*$sql = 'INSERT INTO users (name_u, surname, email, password, role)
                    VALUES (?, ?, ?, ?, ?)';
                
                //Guardar el usuario
                $user = DB::insert($sql,
                                        [
                                            $params_array['name'],
                                            $params_array['surname'],
                                            $params_array['email'],
                                            $pwd,
                                            'ROLE_USER'
                                        ]
                                    );*/

                //Crear el usuario
                $user = new User();
                $user->name_u = $params_array['name_u'];
                $user->surname = $params_array['surname'];
                $user->email = $params_array['email'];
                $user->password = $pwd;
                $user->role = 'ROLE_USER';

                //Guardar el usuario
                $user->save();

                $data = array(
                    'status'    => 'success',
                    'code'      => 200,
                    'message'   => 'El usuario se ha creado corrrectamente',
                    'user'      => $user
                );
            }

        }else{
            $data = array(
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'Los datos enviados no son correctos'
            );
        }


        return response()->json($data, $data['code']);
    }

    public function login(Request $request)
    {
        $jwtAuth = new \JwtAuth(); //Llamar al servicio de JwtAuth (alias) del app.php de "config"

        // Recibir datos por POST
        $json = $request->input('json', null);
        $params = json_decode($json); //Obtener un objeto
        $params_array = json_decode($json, true); //array

        // Validar datos

        //Validar datos con la libreria "\Validator"
        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {

            //La validacion ha fallado
            $signup = array(
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'El usuario no se ha podido identificar',
                'errors'    => $validate->errors()
            );
        }else{

            // Cifrar la password
            $pwd = hash('sha256', $params->password);
            
            //Devolver token o datos
            $signup = $jwtAuth->signup($params->email, $pwd);

            if(!empty($params->getToken)){

                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }
            
        return response()->json($signup, 200);
    } 

    public function update(Request $request){

        // Comprobar si el usuario esta identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        // Actualizar usuario
        // Recoger los datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true); //array

        if($checkToken && !empty($params_array)){

            // Sacar usuario identificado
            $user = $jwtAuth->checkToken($token, true);

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'name_u' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users'.$user->sub
            ]);

            // Quitar los campos que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            // Actualizar usuario en la Base de Datos
            $user_update = User::where('id', $user->sub)->update($params_array);

            //Deveolver un array con resultado
            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'user'      => $user,
                'changes'   => $params_array
            );

        }else{
            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'El usuario no esta identificado.'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request){

        // Recoger datos de la peticion
        $image = $request->file('file0');

        //  Validacion de la imagen
        $validate = \Validator::make($request->all(), [
            'file0'     =>'required|image|mimes:jpg,jpeg,png,gif'
        ]);

        //Guardar imagen
        if(!$image || $validate->fails()){

            $data = array(
                'code'      => 400,
                'status'    => 'error',
                'message'   => 'Error al subir imagen.'
            );

        }else{

            $image_name = time().$image->getClientOriginalName();

            // Para guardar mis imagene en mi carpeta de users dentro de mi carpeta storage
            \Storage::disk('users')->put($image_name, \File::get($image)); 

            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'image'     => $image_name
            );
        }

        return response($data, $data['code']);
    }

    public function getImage($filename){

        $isset = \Storage::disk('users')->exists($filename);
        
        if($isset){
            $file = \Storage::disk('users')->get($filename);

            return new Response($file, 200);
        }else{

            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'message'     => 'La imagen no existe'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function detail($id){

        $user = User::find($id);

        if(is_object($user)){

            $data = array(
                'code'      => 200,
                'status'    => 'success',
                'user'      => $user
            );
        }else{

            $data = array(
                'code'      => 404,
                'status'    => 'error',
                'user'      => 'El usuario no existe'
            );
        }
        
        return response()->json($data, $data['code']);
    }
}
