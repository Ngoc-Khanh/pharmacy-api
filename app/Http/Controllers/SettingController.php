<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: 'v1/admin/settings')]
#[Middleware(middleware: ['jwt.auth', 'role:admin,pharmacist'])]
/**
 * @OA\Tag(
 *     name="Settings",
 *     description="Các API endpoint để quản lý cài đặt"
 * )
 */
class SettingController extends Controller
{
    #[Patch(uri: '/profile-update', name: 'admin.settings.profile-update')]
    /**
     * @OA\Patch(
     *     path="/v1/admin/settings/profile-update",
     *     summary="Cập nhật thông tin profile",
     *     description="Cập nhật firstname và lastname của user hiện tại",
     *     operationId="profileUpdate",
     *     tags={"Settings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Bearer token",
     *         required=true,
     *         @OA\Schema(type="string", example="Bearer your-jwt-token")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="firstname", type="string", description="Tên", example="Nguyễn"),
     *             @OA\Property(property="lastname", type="string", description="Họ", example="Văn A")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thông tin thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Cập nhật thông tin thành công"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="firstname", type="string", example="Nguyễn"),
     *                 @OA\Property(property="lastname", type="string", example="Văn A"),
     *                 @OA\Property(property="email", type="string", example="user@example.com"),
     *                 @OA\Property(property="role", type="string", example="admin"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dữ liệu không hợp lệ"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="data", type="null")
     *         )
     *     )
     * )
     */
    public function profileUpdate(Request $request)
    {
        /**
         * @var \App\Models\User $user
         */
        $user = Auth::user();        
        // Validation
        $validated = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15|unique:users|regex:/^[+]?[0-9]+$/',
        ]);
        $originalFirstname = $user->firstname;
        $originalLastname = $user->lastname;
        $originalPhone = $user->phone;
        $updateData = [];
        if (array_key_exists('firstname', $validated)) {
            if ($validated['firstname'] !== null && trim($validated['firstname']) !== '') $updateData['firstname'] = trim($validated['firstname']);
            else $updateData['firstname'] = $originalFirstname;
        }
        if (array_key_exists('lastname', $validated)) {
            if ($validated['lastname'] !== null && trim($validated['lastname']) !== '') $updateData['lastname'] = trim($validated['lastname']);
            else $updateData['lastname'] = $originalLastname;
        }
        if (array_key_exists('phone', $validated)) {
            if ($validated['phone'] !== null && trim($validated['phone']) !== '') $updateData['phone'] = trim($validated['phone']);
            else $updateData['phone'] = $originalPhone;
        }
        if (!empty($updateData)) $user->update($updateData);
        return $this->json($user, 'Cập nhật thông tin thành công');
    }

    #Póst
}
