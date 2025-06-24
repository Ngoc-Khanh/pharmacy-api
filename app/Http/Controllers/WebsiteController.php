<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    /**
     * Trang chủ API - Hiển thị thông tin API với các phiên bản
     */
    public function home()
    {
        $data = [
            'ten_api' => 'API Cửa hàng Pharmacity',
            'mo_ta' => 'API backend thương mại điện tử cho sản phẩm dược phẩm',
            'phien_ban' => '1.0.0',
            'trang_thai' => 'hoạt_động',
            'moi_truong' => config('app.env'),
            'phien_ban_php' => PHP_VERSION,
            'phien_ban_laravel' => app()->version(),
            'mui_gio' => config('app.timezone'),
            'tai_lieu' => url('/api/docs'),
            'thoi_gian' => now()->toISOString()
        ];

        return $this->json($data, 'Chào mừng đến với API Cửa hàng Pharmacity');
    }

    public function index()
    {
        $data = Website::select('name', 'url')->get()->makeHidden(['id']);
        return response()->json($data);
    }
}
