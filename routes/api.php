<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CiudadanoController;
use App\Http\Controllers\DenunciaController;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    // Solo admin puede crear prestamistas
     Route::post('prestamistas', [AuthController::class, 'createPrestamista']);
     Route::get('prestamistas/lista', [AuthController::class, 'listaPrestamista']);

    //Registro de Ciudadano
     Route::post('ciudadanos', [CiudadanoController::class, 'store']);
     Route::get('ciudadanos/lista', [CiudadanoController::class, 'listaCiudadanos']);
     Route::post('ciudadanos/consultar', [CiudadanoController::class, 'consultar']);


     Route::post('denuncias', [DenunciaController::class, 'store']);
});