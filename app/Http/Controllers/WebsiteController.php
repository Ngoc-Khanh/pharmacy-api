<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index()
    {
        $data = Website::select('name', 'url')->get()->makeHidden(['id']);
        return response()->json($data);
    }
}
