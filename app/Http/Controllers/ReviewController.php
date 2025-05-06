<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\RouteAttributes\Attributes\Post;
use Spatie\RouteAttributes\Attributes\Patch;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Prefix(prefix: "v1/reviews")]
#[Middleware(middleware: 'jwt.auth')]
/**
 * @OA\Tag(
 *     name="Review",
 *     description="Đánh giá sản phẩm"
 * )
 */
class ReviewController extends Controller
{
    public function reviewList() {}

    #[Post('/write', name: 'review.write')]
    /**
     * @OA\Post(
     *     path="/api/v1/reviews/write",
     *     tags={"Review"},
     *     summary="Viết đánh giá",
     *     description="Viết đánh giá cho sản phẩm",
     *     operationId="writeReview",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"medicine_id", "rating", "comment"},
     *             @OA\Property(property="medicine_id", type="string", example="1234567890", description="ID của thuốc"),
     *             @OA\Property(property="rating", type="integer", example=5, description="Đánh giá từ 1-5 sao"),
     *             @OA\Property(property="comment", type="string", example="Sản phẩm rất tốt!", description="Nội dung đánh giá"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Đánh giá đã được thêm thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đánh giá đã được thêm thành công"),
     *             @OA\Property(
     *                 property="data", 
     *                 type="object",
     *                 @OA\Property(property="_id", type="string", example="507f1f77bcf86cd799439011"),
     *                 @OA\Property(property="user_id", type="string", example="507f1f77bcf86cd799439012"),
     *                 @OA\Property(property="medicine_id", type="string", example="507f1f77bcf86cd799439013"),
     *                 @OA\Property(property="rating", type="integer", example=5),
     *                 @OA\Property(property="comment", type="string", example="Sản phẩm rất tốt!"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T00:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Thông tin đánh giá không hợp lệ"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Chưa xác thực",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Không có quyền truy cập"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
    public function writeReview(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|string|exists:medicines,_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $user = Auth::user();
        $data = Review::create([
            'user_id' => $user->_id,
            'medicine_id' => $request->medicine_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);
        return $this->json($data, 'Đánh giá đã được thêm thành công');
    }

    #[Patch('/update/{id}', name: 'review.update')]
    /**
     * @OA\Patch(
     *     path="/api/v1/reviews/update/{id}",
     *     tags={"Review"},
     *     summary="Cập nhật đánh giá",
     *     description="Cập nhật đánh giá sản phẩm (chỉ tác giả mới có quyền cập nhật)",
     *     operationId="updateReview",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID của đánh giá",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"medicine_id", "rating", "comment"},
     *             @OA\Property(property="medicine_id", type="string", example="1234567890", description="ID của thuốc"),
     *             @OA\Property(property="rating", type="integer", example=5, description="Đánh giá từ 1-5 sao"),
     *             @OA\Property(property="comment", type="string", example="Sản phẩm rất tốt!", description="Nội dung đánh giá")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cập nhật thành công",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Đánh giá đã được cập nhật thành công"),
     *             @OA\Property(
     *                 property="data", 
     *                 type="object",
     *                 @OA\Property(property="_id", type="string", example="507f1f77bcf86cd799439011"),
     *                 @OA\Property(property="user_id", type="string", example="507f1f77bcf86cd799439012"),
     *                 @OA\Property(property="medicine_id", type="string", example="507f1f77bcf86cd799439013"),
     *                 @OA\Property(property="rating", type="integer", example=5),
     *                 @OA\Property(property="comment", type="string", example="Sản phẩm rất tốt!"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T00:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Không có quyền cập nhật",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Bạn không có quyền cập nhật đánh giá này"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Đánh giá không tồn tại",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Đánh giá không tồn tại"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Dữ liệu không hợp lệ",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Thông tin đánh giá không hợp lệ"),
     *             @OA\Property(property="data", type="null", example=null)
     *         )
     *     )
     * )
     */
    public function updateReview(Request $request, $id) {
        $review = Review::find($id);
        if (!$review) return $this->fail(null, 'Đánh giá không tồn tại', 404);
        $user = Auth::user();
        if ($review->user_id !== $user->_id) return $this->fail(null, 'Bạn không có quyền cập nhật đánh giá này', 403);
        $validator = Validator::make($request->all(), [
            'medicine_id' => 'required|string|exists:medicines,_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:3|max:1000',
        ]);
        if ($validator->fails()) return $this->fail(null, $validator->errors()->first(), 422);
        $review->update([
            'medicine_id' => $request->medicine_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);
        return $this->json($review, 'Đánh giá đã được cập nhật thành công');
    }
}
