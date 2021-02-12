<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PruebasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Cargando clases
use App\Http\Middleware\ApiAuthMiddleware;

//RUTAS DE PRUEBA
Route::get('/', function () {
    return '<h1>Hola des de laravel</h1>';
});

Route::get('/welcome', function () {
    return view('welcome');
});

Route::get('/pruebas/{nombre?}', function ($nombre = null) {

    $texto = "<h2>Texto desde una ruta</h2>";
    $texto .= 'Nombre: ' . $nombre;
    
    return view('pruebas', array(
        'texto' => $texto
    ));

});

//Crear una ruta para hacer uso de mi controlador
Route::get('/animales', [PruebasController::class, 'index']);
Route::get('/test-orm', [PruebasController::class, 'testOrm']);

// RUTAS DEL API
/*Metodos HTTP comunes
    * GET       conseguir datos o recursos 
    * POST      guardar datos o recursos. O hacer logica desde formualario
    * PUT       actualizar recursos o datos.
    * DELETE    eliminar datos o recursos
*/

// Rutas de prueba
//Route::get('/usuario/pruebas', [UserController::class, 'pruebas']);
//Route::get('/categoria/pruebas', [CategoryController::class, 'pruebas']);
//Route::get('/entrada/pruebas', [PostController::class, 'pruebas']);

// Rutas del controlador de usuarios
Route::post('/api/register', [UserController::class, 'register']);
Route::post('/api/login', [UserController::class, 'login']);
Route::put('/api/user/update', [UserController::class, 'update']);
Route::post('/api/user/upload', [UserController::class, 'upload'])->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', [UserController::class, 'getImage']);
Route::get('/api/user/detail/{id}', [UserController::class, 'detail']);

// Rutas del controlador de categorias
Route::resource('/api/category', 'CategoryController');

// Rutas del controlador posts (entradas)
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload', [PostController::class, 'upload']);
Route::get('/api/post/image/{filename}', [PostController::class, 'getImage']);
Route::get('/api/post/category/{id}', [PostController::class, 'getPostsByCategory']);
Route::get('/api/post/user/{id}', [PostController::class, 'getPostsByUser']);