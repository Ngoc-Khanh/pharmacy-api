<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v2/medicine')]
class MedicineController extends Controller
{
    #[Get('/popular-medicine', "medicine.popular")]
    public function getPopularMedicine()
    {
        $popularMedicines = Medicine::with(['category', 'supplier'])
            ->where('ratings.liked', '>=', 10)
            ->orderBy('ratings.liked', 'desc')
            ->limit(4)
            ->get()
            ->makeHidden(['category_id', 'supplier_id', 'details', 'usageguide','created_at', 'updated_at']);
        $popularMedicines->each(function ($medicine) {
            if ($medicine->category) $medicine->category->makeHidden(['created_at', 'updated_at']);
            if ($medicine->supplier) $medicine->supplier->makeHidden(['created_at', 'updated_at']);
        });

        return $this->json($popularMedicines, 'Popular medicines fetched successfully', 200);
    }

    #[Get('/medicine-list', "medicine.list")]
    public function getAllMedicine()
    {
        $medicines = Medicine::with(['category', 'supplier'])->get()->makeHidden(['category_id', 'supplier_id', 'details', 'usageguide','created_at', 'updated_at']);
        $medicines->each(function ($medicine) {
            if ($medicine->category) $medicine->category->makeHidden(['created_at', 'updated_at']);
            if ($medicine->supplier) $medicine->supplier->makeHidden(['created_at', 'updated_at']);
        });
        return $this->json($medicines, 'Medicines fetched successfully', 200);
    }

    #[Get('/detail/{medicineId}', "medicine.detail")]
    public function getDetailMedicine($medicineId)
    {
        $medicine = Medicine::with(['category', 'supplier'])
            ->find($medicineId)
            ?->makeHidden(['category_id', 'supplier_id', 'created_at', 'updated_at']);
        if ($medicine) {
            $medicine->category?->makeHidden(['created_at', 'updated_at']);
            $medicine->supplier?->makeHidden(['created_at', 'updated_at']);
            return $this->json($medicine, 'Medicine fetched successfully', 200);
        }
        return $this->fail([], 'Medicine not found', 404);
    }

    #[Post('/admin/medicine/add-medicine-step-one', "medicine.add")]
    public function addMedicineStepOne(Request $request)
    {
        if ($request->user()->role !== 'admin') return $this->fail([], 'Unauthorized', 401);
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string|max:255',
            'supplier_id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'priority' => 'required|integer|min:0',
            'description' => 'required|string',
            'profile_image.image_url' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) return $this->fail([], $validator->errors(), 422);
        $medicine = Medicine::create([
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'priority' => $request->priority,
            'description' => $request->description,
        ]);
        return $this->json($medicine, 'Medicine added successfully', 200);
    }
}
