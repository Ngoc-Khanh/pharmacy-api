<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix("v2/category")]
class CategoryController extends Controller
{
    #[Get('/category-list', "category.list")]
    public function getAllCategory()
    {
        $category = Category::all();
        return $this->json($category, 'Category fetched successfully', 200);
    }
}
