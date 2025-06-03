<?php

namespace App\Http\Requests;

use App\Utils\HttpResponse;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reset_token' => 'required|string',
            'password' => [
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
            'reset_token.required' => 'Mã xác thực là thông tin bắt buộc.',
            'password.required' => 'Mật khẩu là thông tin bắt buộc.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ cái thường, một chữ cái hoa, một số và một ký tự đặc biệt.',
        ];
    }

    public function failedValidation(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json(
                HttpResponse::toJson(
                    data: null,
                    message: 'Dữ liệu không hợp lệ',
                    status: 422,
                    errors: $validator->errors()
                ),
                422
            )
        );
    }
}
