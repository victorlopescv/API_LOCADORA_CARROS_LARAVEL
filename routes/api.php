<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('v1')->middleware('jwt.auth')->group( function(){
    Route::post('me', '\App\Http\Controllers\AuthController@me');
    Route::post('refresh', '\App\Http\Controllers\AuthController@refresh');
    Route::post('logout', '\App\Http\Controllers\AuthController@logout');
    Route::apiResource('/cliente','\App\Http\Controllers\ClienteController');
    Route::apiResource('/carro','\App\Http\Controllers\CarroController');
    Route::apiResource('/locacao','\App\Http\Controllers\LocacaoController');
    Route::apiResource('/marca','\App\Http\Controllers\MarcaController');
    Route::apiResource('/modelo','\App\Http\Controllers\ModeloController');
});



/*na versão 8 do Laravel temos que informar o namespace completo do controller (\App\Http\controllers\<nome_controlador>)
para não precisar informar o nome completo temos que ir em providers/RouteServiceProvider.php e descomentar o namespace
dessa forma não é necessario informar completo... porém neste projeto vamos utilizar o nome completo
*/
Route::post('login', '\App\Http\Controllers\AuthController@login');



