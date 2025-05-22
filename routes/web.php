<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Website routes
Route::get('/', [WebsiteController::class, 'index']);
