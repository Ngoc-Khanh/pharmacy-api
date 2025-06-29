<?php

namespace App\Http\Controllers;

use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: "v1/admin/excel")]
#[Middleware(middleware: "jwt.auth")]
/**
 * @OA\Tag(
 *     name="Excel",
 *     description="Các API endpoint để quản lý excel"
 * )
 */
class ExcelController extends Controller
{
    protected ExcelExportService $excelExportService;
    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    /**
     * Thêm CORS headers cho response
     */
    private function addCorsHeaders($response)
    {
        // Kiểm tra nếu là BinaryFileResponse (download response)
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, Origin');
            $response->headers->set('Access-Control-Expose-Headers', 'Content-Disposition, Content-Type, Content-Length');
            return $response;
        }
        
        // Cho các response khác
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type, X-Requested-With, Origin')
            ->header('Access-Control-Expose-Headers', 'Content-Disposition, Content-Type, Content-Length');
    }

    /**
     * @OA\Get(
     *     path="/v1/admin/excel/export-all",
     *     summary="Xuất tất cả dữ liệu hệ thống",
     *     description="Xuất tất cả dữ liệu (users, suppliers, medicines, orders, categories, invoices, reviews) ra file Excel",
     *     tags={"Excel Export"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="File Excel được tải xuống thành công",
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server"
     *     )
     * )
     */
    #[Get(uri: 'export-all')]
    public function exportAll()
    {
        try {
            $response = $this->excelExportService->exportAll();
            // Thêm CORS headers manually
            return $this->addCorsHeaders($response);
        } catch (\Exception $e) {
            return $this->fail(null,'Lỗi khi xuất dữ liệu: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/v1/admin/excel/export/{module}",
     *     summary="Xuất dữ liệu theo module",
     *     description="Xuất dữ liệu của một module cụ thể (users, suppliers, medicines, orders, categories, invoices, reviews)",
     *     tags={"Excel Export"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="module",
     *         in="path",
     *         required=true,
     *         description="Tên module cần xuất",
     *         @OA\Schema(
     *             type="string",
     *             enum={"users", "suppliers", "medicines", "orders", "categories", "invoices", "reviews"}
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="File Excel được tải xuống thành công",
     *         @OA\MediaType(
     *             mediaType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Module không hợp lệ"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Lỗi server"
     *     )
     * )
     */
    #[Get(uri: 'export/{module}')]
    public function exportByModule(string $module)
    {
        try {
            $allowedModules = ['users', 'suppliers', 'medicines', 'orders', 'categories', 'invoices'];
            if (!in_array($module, $allowedModules)) return $this->fail(null,'Module không hợp lệ. Các module được hỗ trợ: ' . implode(', ', $allowedModules), 400);
            $response = $this->excelExportService->exportByModule($module);
            // Thêm CORS headers manually
            return $this->addCorsHeaders($response);
        } catch (\Exception $e) {
            return $this->fail(null,'Lỗi khi xuất dữ liệu: ' . $e->getMessage(), 500);
        }
    }
}
