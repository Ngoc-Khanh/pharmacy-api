<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v2/medicine')]
class MedicineController extends Controller
{
    #[Get('/medicine-list', "medicine.list")]
    public function getAllMedicine()
    {
        $medicines = Medicine::with(['category', 'supplier'])->get()->makeHidden(['category_id', 'supplier_id', 'created_at', 'updated_at']);
        $medicines->each(function ($medicine) {
            if ($medicine->category) $medicine->category->makeHidden(['created_at', 'updated_at']);
            if ($medicine->supplier) $medicine->supplier->makeHidden(['created_at', 'updated_at']);
        });
        return $this->json($medicines, 'Medicines fetched successfully', 200);
    }
}
