<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;

// Route::get('/', function () {
//     return view('welcome');
// });

// Website routes - API Home Page
Route::get('/', [WebsiteController::class, 'home']);
