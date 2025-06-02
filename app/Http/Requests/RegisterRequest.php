<?php

namespace App\Http\Requests;

use App\Utils\HttpResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
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
            'phone' => 'required|string|max:15|unique:users',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'firstname.required' => 'Vui lòng nhập họ',
            'firstname.string' => 'Họ phải là chuỗi ký tự',
            'firstname.max' => 'Họ không được vượt quá 255 ký tự',
            'lastname.required' => 'Vui lòng nhập tên',
            'lastname.string' => 'Tên phải là chuỗi ký tự',
            'lastname.max' => 'Tên không được vượt quá 255 ký tự',
            'username.required' => 'Vui lòng nhập tên đăng nhập',
            'username.string' => 'Tên đăng nhập phải là chuỗi ký tự',
            'username.min' => 'Tên đăng nhập phải có ít nhất 3 ký tự',
            'username.max' => 'Tên đăng nhập không được vượt quá 255 ký tự',
            'username.unique' => 'Tên đăng nhập này đã được sử dụng',
            'email.required' => 'Vui lòng nhập địa chỉ email',
            'email.string' => 'Email phải là chuỗi ký tự',
            'email.email' => 'Email không đúng định dạng',
            'email.max' => 'Email không được vượt quá 255 ký tự',
            'email.unique' => 'Địa chỉ email này đã được đăng ký',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ cái thường, một chữ cái hoa, một chữ số và một ký tự đặc biệt (@$!%*#?&)',
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.string' => 'Số điện thoại phải là chuỗi ký tự',
            'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự',
            'phone.unique' => 'Số điện thoại này đã được đăng ký',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new HttpResponseException(
            response()->json(
                HttpResponse::toJson(
                    data: null,
                    message: "Dữ liệu không hợp lệ",
                    status: 422,
                    locale: "vi_VN",
                    errors: $validator->errors()
                ),
                422
            )
        );
    }
}
