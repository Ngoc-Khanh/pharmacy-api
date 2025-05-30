<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'firstname.max' => 'Họ không được vượt quá 255 ký tự',
            'lastname.required' => 'Vui lòng nhập tên',
            'lastname.max' => 'Tên không được vượt quá 255 ký tự',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Địa chỉ email này đã được đăng ký',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp',
            'password.regex' => 'Mật khẩu phải chứa ít nhất một chữ cái thường, một chữ cái hoa, một số và một ký tự đặc biệt',
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'phone.unique' => 'Số điện thoại này đã được đăng ký',
            'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự',
        ];
    }
}
