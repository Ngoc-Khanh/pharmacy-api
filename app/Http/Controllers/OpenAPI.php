<?php

namespace App\Http\Controllers;

/**
 * @OA\Schema(
 *     schema="StandardResponse",
 *     type="object",
 *     title="Cấu trúc phản hồi chuẩn",
 *     @OA\Property(property="data", type="object", description="Dữ liệu trả về"),
 *     @OA\Property(property="message", type="string", description="Thông báo từ hệ thống"),
 *     @OA\Property(property="status", type="integer", description="Mã trạng thái HTTP")
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     title="Cấu trúc phản hồi lỗi",
 *     @OA\Property(property="data", type="array", @OA\Items(), description="Dữ liệu rỗng"),
 *     @OA\Property(property="message", type="string", description="Thông báo lỗi"),
 *     @OA\Property(property="status", type="integer", description="Mã trạng thái HTTP")
 * )
 * 
 * @OA\Parameter(
 *     parameter="page",
 *     name="page",
 *     in="query",
 *     description="Số trang hiện tại",
 *     @OA\Schema(type="integer", default=1, minimum=1)
 * )
 * 
 * @OA\Parameter(
 *     parameter="limit",
 *     name="limit",
 *     in="query",
 *     description="Số lượng bản ghi trên một trang",
 *     @OA\Schema(type="integer", default=10, minimum=1, maximum=100)
 * )
 * 
 * @OA\Parameter(
 *     parameter="sort",
 *     name="sort",
 *     in="query",
 *     description="Sắp xếp theo trường (thêm - phía trước để sắp xếp giảm dần)",
 *     @OA\Schema(type="string", example="created_at hoặc -created_at")
 * )
 */
class OpenAPI
{
    // File này chỉ chứa các annotations cho Swagger/OpenAPI
}