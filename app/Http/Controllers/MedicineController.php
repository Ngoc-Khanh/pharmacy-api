<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v2/medicine')]
class MedicineController extends Controller
{
    #[Get('/popular-medicine', "medicine.popular")]
    public function getPopularMedicine() 
    {
        // Option 1: Get medicines with the highest purchase count
        $popularMedicines = Medicine::with(['category', 'supplier'])
            ->where('ratings.liked', '>=', 10)
            ->orderBy('ratings.liked', 'desc')
            ->limit(4)
            ->get()
            ->makeHidden(['category_id', 'supplier_id', 'created_at', 'updated_at']);
        
        // Option 2: Get medicines marked as popular (if you have a 'is_popular' field)
        // $popularMedicines = Medicine::with(['category', 'supplier'])
        //     ->where('is_popular', true)
        //     ->limit(4)
        //     ->get()
        //     ->makeHidden(['category_id', 'supplier_id', 'created_at', 'updated_at']);
        
        // Clean up related data
        $popularMedicines->each(function ($medicine) {
            if ($medicine->category) $medicine->category->makeHidden(['created_at', 'updated_at']);
            if ($medicine->supplier) $medicine->supplier->makeHidden(['created_at', 'updated_at']);
        });
        
        return $this->json($popularMedicines, 'Popular medicines fetched successfully', 200);
    }
    
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
