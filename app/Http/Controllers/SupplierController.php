<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v2/supplier')]
class SupplierController extends Controller
{
    #[Get('/supplier-list', "supplier.list")]
    public function getAllSupplier()
    {
        $suppliers = Supplier::all();
        return $this->json($suppliers, 'Suppliers fetched successfully', 200);
    }
}
