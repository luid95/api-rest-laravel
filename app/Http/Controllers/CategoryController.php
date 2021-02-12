<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;

class CategoryController extends Controller
{

    public function __construct()
    {
        // Indicar en que metodos no aplicar el middleware
        $this->middleware('api.auth', ['except' => ['index', 'show']]);
    }

    // Obtener todas las categorias que hay en  nuestra base de datos
    public function index()
    {
        $categories = Category::all();

        return response()->json([
            'code'          => 200,
            'status'        => 'success',
            'categories'    => $categories
        ]);
    }

    // Obtener una categoria
    public function show($id)
    {
        $category = Category::find($id);

        if (is_object($category)) {

            $data = [
                'code'          => 200,
                'status'        => 'success',
                'category'    => $category
            ];
        } else {

            $data = [
                'code'          => 404,
                'status'        => 'error',
                'message'    => 'La categoria no existe.'
            ];
        }

        return response()->json($data, $data['code']);
    }

    // Guardar una categoria
    public function store(Request $request)
    {
        // Recoger los datos por POST
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if (!empty($params_array)) {

            // Validar los datos
            $validate = \Validator::make($params_array, [
                'name_c'  => ' required'
            ]);

            // Guardar la categoria
            if ($validate->fails()) {

                $data = [
                    'code'       => 404,
                    'status'     => 'error',
                    'message'    => 'No se ha guardado la categoria.'
                ];
            } else {

                $category = new Category();
                $category->name_c = $params_array['name_c'];
                $category->save();

                $data = [
                    'code'       => 200,
                    'status'     => 'success',
                    'message'    => $category
                ];
            }
        } else {

            $data = [
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'No has enviado ninguna categoria.'
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

        if (!empty($params_array)) {

            // Validar datos
            $validate = \Validator::make($params_array, [
                'name_c' => 'required'
            ]);

            // Quitar lo que no quiero actualizar
            unset($params_array['id']);
            unset($params_array['created_at']);

            // Actualizar el registro (categoria)
            $category = Category::where('id', $id)->update($params_array);

            $data = [
                'code'       => 200,
                'status'     => 'success',
                'category'    => $params_array
            ];
        } else {

            $data = [
                'code'       => 404,
                'status'     => 'error',
                'message'    => 'No has enviado ninguna categoria.'
            ];
        }

        // Devolver los datos
        return response()->json($data, $data['code']);
    }

}
