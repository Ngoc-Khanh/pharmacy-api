<?php

namespace App\Http\Requests;

use App\Utils\HttpResponse;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string|min:8',
            'new_password' => [
                'required',
                'string',
                'min:8', // Ít nhất 8 ký tự
                'confirmed',
                'regex:/[a-z]/',      // Ít nhất một chữ cái thường
                'regex:/[A-Z]/',      // Ít nhất một chữ cái hoa
                'regex:/[0-9]/',      // Ít nhất một số
                'regex:/[@$!%*#?&]/', // Ít nhất một ký tự đặc biệt
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Mật khẩu hiện tại là thông tin bắt buộc.',
            'current_password.string' => 'Mật khẩu hiện tại phải là chuỗi ký tự hợp lệ.',
            'current_password.min' => 'Mật khẩu hiện tại phải có ít nhất 8 ký tự.',

            'new_password.required' => 'Mật khẩu mới là thông tin bắt buộc.',
            'new_password.string' => 'Mật khẩu mới phải là chuỗi ký tự hợp lệ.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không khớp với mật khẩu mới. Vui lòng nhập lại.',
            'new_password.regex' => [
                'Mật khẩu mới không đủ mạnh. Mật khẩu phải bao gồm:',
                '- Ít nhất một chữ cái thường (a-z)',
                '- Ít nhất một chữ cái hoa (A-Z)',
                '- Ít nhất một chữ số (0-9)',
                '- Ít nhất một ký tự đặc biệt (@$!%*#?&)',
                'Ví dụ: MyPassword123!'
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'mật khẩu hiện tại',
            'new_password' => 'mật khẩu mới',
            'new_password_confirmation' => 'xác nhận mật khẩu mới',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json(
                HttpResponse::toJson(
                    data: null,
                    message: 'Dữ liệu không hợp lệ',
                    status: 422,
                    errors: $validator->errors()->all()
                ),
                422
            )
        );
    }
}
