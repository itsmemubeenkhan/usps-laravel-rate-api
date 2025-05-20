<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShippingController;

Route::get('/shipping-form', [ShippingController::class, 'showForm']);
Route::post('/get-usps-rates', [ShippingController::class, 'getRates'])->name('usps.getRates');



Route::get('/', function () {
    return view('welcome');
});
