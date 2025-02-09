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
            'Laravel' => 'v11.41.3',
            'PHP'=> 'v8.3.16',
        ];
        return $this->json($data, "Connect successful!", 200);
    }
}
