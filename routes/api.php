<?php

use App\Http\Controllers\Api\ProductApiController;
 use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\API\PrinterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
 

 
Route::post('/product_import', [ProductApiController::class, 'importProducts']);
Route::match(['put','patch'], '/update_products/{product}', [ProductApiController::class, 'updateProducts']);
Route::get('/delete_products/{product}', [ProductApiController::class, 'destroyProducts']);




Route::post('/create_category', [CategoryApiController::class, 'storeCategory']); 
Route::post('/update_category/{category}', [CategoryApiController::class, 'updateCategory']);  
Route::get('/delete_category/{category}', [CategoryApiController::class, 'destroyCategory']);
 




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
   
    return $request->user();
});

