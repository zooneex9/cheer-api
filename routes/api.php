<?php

use App\Http\Controllers\API\RegisterController;
use App\Http\Controllers\API\UserController;

use App\Http\Controllers\API\FileUploadController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\QuotationController;

use App\Models\Quotation;

use App\Http\Controllers\Auth\PasswordResetController;

use Illuminate\Support\Facades\Route;

use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;

Route::post('register',[RegisterController::class, 'register']);
Route::post('login',[RegisterController::class, 'login']);

Route::get('/quotation-pdf/{uuid}', function ($uuid) {

    $quotation = Quotation::where('uuid', $uuid)->firstOrFail();
    $products = json_decode($quotation->items, true);

    $total = 0;
    foreach ($products as $product) {
        $total = $total + $product['price'];
    }

    $total = number_format($total, 2, '.', ',');

    $pdf = new Dompdf();
    $pdf->set_option('isHtml5ParserEnabled', true);
    $pdf->set_option('isRemoteEnabled', true);
    $pdf->loadHtml(View::make('quotation', compact('products', 'total'))->render());
    $pdf->setPaper('A4', 'portrait');
    $pdf->render();

    return $pdf->stream($quotation->name . '-' . $quotation->last_name . '.pdf');
});


// Password routes
Route::post('password/create', [PasswordResetController::class, 'create']);
Route::get('password/find/{token}', [PasswordResetController::class, 'find']);
Route::post('password/reset', [PasswordResetController::class, 'reset']);

// Product public
Route::get('product_list' , [ProductController::class, 'index']);
Route::post('quotation_form' , [QuotationController::class, 'store']);

Route::middleware('auth:api')->group( function () {
    // MODEL RESOURCES
    Route::resource('product',  ProductController::class);
    Route::resource('quotation',  QuotationController::class);

    Route::get('roles' , [UserController::class, 'roles']);
    Route::get('users' , [UserController::class, 'index']);
    
    // User profile
    Route::get('logged_in' , [UserController::class, 'loggedIn']);
    Route::post('user_new' , [UserController::class, 'create_user']);

    // Users
    Route::get('user/{id}' , [UserController::class, 'getUser']);
    Route::delete('users/{id}' , [UserController::class, 'destroy']);

    //Files
    Route::post('upload_image',[FileUploadController::class, 'upload_image']);
});