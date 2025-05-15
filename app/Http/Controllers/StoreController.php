<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;

#[Prefix('v1/store')]
class StoreController extends Controller
{
    #[Get('/medicines', name: 'store.medicines')]
    public function Medicines(Request $request)
    {
        // Get all medicines with pagination
        $medicines = Medicine::with('category')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->json($medicines, "Lấy danh sách thuốc thành công");
    }

    #[Get('/medicines/{id}/details', name: 'store.medicines.show')]
    public function MedicineDetails($id)
    {
        // Get medicine details by ID
        $medicine = Medicine::with('category')
            ->where('id', $id)
            ->first();
        if (!$medicine) return $this->json(null, "Không tìm thấy thuốc", 404);
        return $this->json($medicine, "Lấy thông tin thuốc thành công");
    }

    #[Get('/categories', name: 'store.categories')]
    public function RootCategories()
    {
        $categories = Category::all();
        return $this->json($categories, "Lấy toàn bộ danh mục thành công");
    }

    #[Get('/popular-medicine', name: 'store.popular-medicine')]
    public function PopularMedicine()
    {
        // Get top 4 medicines with highest likes and star ratings
        $medicines = Medicine::orderBy('ratings.liked', 'desc')
            ->orderBy('ratings.star', 'desc')
            ->limit(4)
            ->get();

        return $this->json($medicines, "Lấy 4 sản phẩm thuốc có lượt thích và đánh giá cao nhất thành công");
    }

    #[Patch('/account/update-profile', name: 'store.account.update-profile')]
    public function updateProfile() {}
}
