<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;

class ApiController extends Controller
{
    public function index()
    {
        $data = [
            'welcome' => 'Welcome to my Pharmacy API',
            'Author' => 'Krug',
            'email' => 'dongockhanh2003@gmail.com',
        ];
        return $this->json($data, "Connect successful!", 200);
    }
}
