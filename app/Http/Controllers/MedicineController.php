<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\RouteAttributes\Attributes\Delete;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix(prefix: 'v1/admin/medicines')]
#[Middleware(middleware: ['jwt.auth', 'role:admin,pharmacist'])]
class MedicineController extends Controller
{
    #[Post('/add', name: 'admin.medicines.add')]
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string|exists:categories,_id',
            'supplier_id' => 'required|string|exists:suppliers,_id',
            'name' => 'required|string|min:3|max:255',
            // 'thumbnail.image_url' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            // 'description' => 'required|string|min:3|max:1000',
            // 'variants' => 'required|json',
            // 'variants.price' => 'required|numeric|min:0',
            // 'variants.limit_quantity' => 'required|integer|min:0',
            // 'variants.stock_status' => 'required|string|in:IN-STOCK,OUT-OF-STOCK,PRE-ORDER',
            // 'variants.original_price' => 'required|numeric|min:0',
            // 'variants.discount_percent' => 'required|numeric|min:0|max:100',
            // 'variants.is_discount' => 'required|boolean',
            // 'variants.is_featured' => 'required|boolean',
            // 'variants.is_active' => 'required|boolean',
            // 'details' => 'required|json',
            // 'details.ingredients' => 'required|string|min:3|max:1000',
            // 'usage' => 'required|array',
            // 'paramaters.origin' => 'required|string|min:3|max:50',
            // 'paramaters.packaging' => 'required|string|min:3|max:100',
            // 'usageguide.dosage.adult' => 'required|string|min:3|max:100',
            // 'usageguide.dosage.child' => 'required|string|min:3|max:100',
            // 'usageguide.directions' => 'required|array',
            // 'usageguide.precautions' => 'required|array',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $data = Medicine::create([
            'category_id' => $request->category_id,
            'supplier_id' => $request->supplier_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            // 'thumbnail' => $request->thumbnail,
            // 'description' => $request->description,
            // 'variants' => json_decode($request->variants, true),
            // 'ratings' => [
            //     'total_ratings' => 0,
            //     'total_reviews' => 0,
            //     'average_rating' => 0
            // ],
            // 'details' => json_decode($request->details, true),
            // 'usageguide' => json_decode($request->usageguide, true),
            // 'created_by' => auth()->user()->_id
        ]);
        return $this->json($data, 'Đã thêm thuốc thành công', 201);
    }

    #[Delete('/delete/{id}', name: 'admin.medicines.delete')]
    public function deleteMedicine($id)
    {
        $medicine = Medicine::find($id);
        if (!$medicine) return $this->fail(null, 'Không tìm thấy thuốc', 404);
        $medicine->delete();
        return $this->json(null, 'Đã xóa thuốc thành công', 200);
    }
}
