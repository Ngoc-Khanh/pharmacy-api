<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        /** @var \App\Models\User $user */
        $user = Auth::user();        
        $validated = $request->validate([
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15|unique:users|regex:/^[+]?[0-9]+$/',
        ]);
        $updateData = [];
        if (array_key_exists('firstname', $validated) && $validated['firstname'] !== null && trim($validated['firstname']) !== '') {
            $newFirstname = trim($validated['firstname']);
            if ($newFirstname !== $user->firstname) $updateData['firstname'] = $newFirstname;
        }
        if (array_key_exists('lastname', $validated) && $validated['lastname'] !== null && trim($validated['lastname']) !== '') {
            $newLastname = trim($validated['lastname']);
            if ($newLastname !== $user->lastname) $updateData['lastname'] = $newLastname;
        }
        if (array_key_exists('phone', $validated) && $validated['phone'] !== null && trim($validated['phone']) !== '') {
            $newPhone = trim($validated['phone']);
            if ($newPhone !== $user->phone) $updateData['phone'] = $newPhone;
        }
        if (empty($updateData)) return $this->json($user, 'Không có thông tin nào được thay đổi');
        $user->update($updateData);
        return $this->json($user, 'Cập nhật thông tin thành công');
    }

    #[Patch(uri: '/account-update', name: 'admin.settings.account-update')]
    /**
     * @OA\Patch(
     *     path="/v1/admin/settings/account-update",
     *     operationId="adminAccountUpdate",
     *     tags={"Admin Settings"},
     *     summary="Cập nhật thông tin tài khoản admin",
     *     description="Cập nhật username và email của admin đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="admin123", description="Tên đăng nhập mới (tùy chọn)"),
     *             @OA\Property(property="email", type="string", example="admin@example.com", description="Email mới (tùy chọn)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thông tin thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="username", type="string", example="admin123"),
     *                 @OA\Property(property="email", type="string", example="admin@example.com"),
     *                 @OA\Property(property="firstname", type="string", example="Admin"),
     *                 @OA\Property(property="lastname", type="string", example="User"),
     *                 @OA\Property(property="role", type="string", example="admin")
     *             ),
     *             @OA\Property(property="message", type="string", example="Cập nhật thông tin thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Username đã được sử dụng"),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Unauthorized"),
     *             @OA\Property(property="status", type="integer", example=401),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     )
     * )
     */
    public function accountUpdate(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $validated = $request->validate([
            'username' => 'nullable|string|max:255|unique:users',
            'email' => 'nullable|email|max:255|unique:users',
        ]);
        $updateData = [];
        if (array_key_exists('username', $validated) && $validated['username'] !== null && trim($validated['username']) !== '') {
            $newUsername = trim($validated['username']);
            if ($newUsername !== $user->username) $updateData['username'] = $newUsername;
        }
        if (array_key_exists('email', $validated) && $validated['email'] !== null && trim($validated['email']) !== '') {
            $newEmail = trim($validated['email']);
            if ($newEmail !== $user->email) $updateData['email'] = $newEmail;
        }
        if (empty($updateData)) return $this->json($user, 'Không có thông tin nào được thay đổi');
        $user->update($updateData);
        return $this->json($user, 'Cập nhật thông tin thành công');
    }

    #[Patch(uri: '/password-update', name: 'admin.settings.password-update')]
    /**
     * @OA\Patch(
     *     path="/v1/admin/settings/password-update",
     *     operationId="adminPasswordUpdate",
     *     tags={"Admin Settings"},
     *     summary="Cập nhật mật khẩu admin",
     *     description="Cập nhật mật khẩu của admin đã đăng nhập",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "new_password", "new_password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="OldPassword123!", description="Mật khẩu hiện tại"),
     *             @OA\Property(property="new_password", type="string", example="NewPassword456@", description="Mật khẩu mới - phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt"),
     *             @OA\Property(property="new_password_confirmation", type="string", example="NewPassword456@", description="Xác nhận mật khẩu mới")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật mật khẩu thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string", example="550e8400-e29b-41d4-a716-446655440000"),
     *                 @OA\Property(property="username", type="string", example="admin123"),
     *                 @OA\Property(property="email", type="string", example="admin@example.com"),
     *                 @OA\Property(property="firstname", type="string", example="Admin"),
     *                 @OA\Property(property="lastname", type="string", example="User"),
     *                 @OA\Property(property="role", type="string", example="admin")
     *             ),
     *             @OA\Property(property="message", type="string", example="Cập nhật mật khẩu thành công"),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Mật khẩu hiện tại không chính xác",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Mật khẩu hiện tại không chính xác"),
     *             @OA\Property(property="status", type="integer", example=401),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Mật khẩu mới phải có ít nhất 8 ký tự"),
     *             @OA\Property(property="status", type="integer", example=422),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền truy cập",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string", example="Forbidden"),
     *             @OA\Property(property="status", type="integer", example=403),
     *             @OA\Property(property="locale", type="string", example="vi_VN"),
     *             @OA\Property(property="error", type="null")
     *         )
     *     )
     * )
     */
    public function passwordUpdate(ChangePasswordRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        if (!Hash::check($validated['current_password'], $user->password)) return $this->fail(null, 'Mật khẩu hiện tại không chính xác', 401);
        $user->password = Hash::make($validated['new_password']);
        /** @var \App\Models\User $user */
        $user->save();
        return $this->json($user, 'Cập nhật mật khẩu thành công');
    }
}
