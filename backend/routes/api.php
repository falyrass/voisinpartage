<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArticleController;
// Endpoint simple de test
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

// Liste d'items mock (pas de DB)
Route::get('/items-mock', function (Request $request) {
    $lat = (float) ($request->query('lat', 45.5017));
    $lon = (float) ($request->query('lon', -73.5673));
    return [
        ['id' => 1, 'title' => 'Perceuse', 'lat' => $lat + 0.003, 'lon' => $lon + 0.004, 'status' => 'available'],
        ['id' => 2, 'title' => 'Tondeuse', 'lat' => $lat - 0.004, 'lon' => $lon + 0.002, 'status' => 'available'],
    ];
});

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/items', [ArticleController::class, 'index']);
Route::post('/items', [ArticleController::class, 'store']);
