<?php
namespace App\Helpers;//Definir el namespace

use Firebase\JWT\JWT; //Hacer uso de las funciones de la libreria
use Illuminate\Support\Facades\DB; //libreria base de datos
//importamos el modelo de usuario
use App\Models\User;

use function PHPSTORM_META\type;

class JwtAuth{

    public $key;

    public function __construct()
    {
        $this->key = "esta_es_una_clave_super_secreta";
    }

    public function signup($email, $password, $getToken = null){
        //Si existe el usuario con sus credenciales
        /*$sql = 'SELECT * FROM users WHERE users.email=? AND users.password=?';
        $user = DB::select($sql,
                                    [
                                        $email,
                                        $password
                                    ]
                                );*/
        $user= User::where([
            'email' => $email,
            'password' => $password
        ])->first(); // Se usa el metodo first para obtener los datos de un objeto.

        //Comprobar si son correctas
        $signup = false;

        //VERIFICAR SI EL SIZEOF DEL ARRAY ES IGUAL A 1 
        if(is_object($user)){
            // En el caso de que los datos sean correctos 
            $signup = true;
            
        }

        //Generar el token con los datos del usuario identificado
        if($signup){


            $token = array(
                // sub hace referencia al id del usuario o registro
                'sub'       =>  $user->id,
                'email'     =>  $user->email,
                'name_u'    =>  $user->name_u,
                'surname'   =>  $user->surname,
                'description' => $user->description,
                'image'     =>  $user->image,
                'iat'       =>  time(), // Cuando se ha creado el token
                'exp'       =>  time() + (7* 24* 60* 60) // Caducidad del token (dias * horas* minutos * segundos)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');

            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            //Devolver los datos decodificados o el token en funcion de un parametro
            if(is_null($getToken)){
                $data = $jwt;
            }else{
                $data = $decoded;
            }

        }else{

            $data = array(
                'status'    => 'error',
                'message'   => 'Login incorrecto.'
            );
        }

        return $data;
    }

    public function checkToken($jwt, $getIdentity = false){
        $auth = false;

        try {
            $jwt = str_replace('"', '', $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\UnexpectedValueException $e) {
            $auth = false;
        } catch (\DomainException $e){
            $auth = false;
        }

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity){
            return $decoded;
        }


        return $auth;

    }

}