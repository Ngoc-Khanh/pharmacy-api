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
            'phone' => 'required|string|max:15|unique:users|regex:/^[+]?[0-9]+$/',
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
            'firstname.required' => 'Họ là thông tin bắt buộc, vui lòng nhập đầy đủ',
            'firstname.string' => 'Họ chỉ được chứa các ký tự hợp lệ',
            'firstname.max' => 'Họ quá dài, vui lòng nhập tối đa 255 ký tự',
            'lastname.required' => 'Tên là thông tin bắt buộc, vui lòng nhập đầy đủ',
            'lastname.string' => 'Tên chỉ được chứa các ký tự hợp lệ',
            'lastname.max' => 'Tên quá dài, vui lòng nhập tối đa 255 ký tự',
            'username.required' => 'Tên đăng nhập là thông tin bắt buộc',
            'username.string' => 'Tên đăng nhập chỉ được chứa các ký tự hợp lệ',
            'username.min' => 'Tên đăng nhập quá ngắn, vui lòng nhập ít nhất 3 ký tự',
            'username.max' => 'Tên đăng nhập quá dài, vui lòng nhập tối đa 255 ký tự',
            'username.unique' => 'Tên đăng nhập này đã có người sử dụng, vui lòng chọn tên khác',
            'email.required' => 'Địa chỉ email là thông tin bắt buộc',
            'email.string' => 'Email chỉ được chứa các ký tự hợp lệ',
            'email.email' => 'Địa chỉ email không đúng định dạng, vui lòng kiểm tra lại',
            'email.max' => 'Email quá dài, vui lòng nhập tối đa 255 ký tự',
            'email.unique' => 'Email này đã được đăng ký, vui lòng sử dụng email khác',
            'password.required' => 'Mật khẩu là thông tin bắt buộc',
            'password.string' => 'Mật khẩu chỉ được chứa các ký tự hợp lệ',
            'password.min' => 'Mật khẩu quá yếu, vui lòng nhập ít nhất 8 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp, vui lòng nhập lại',
            'password.regex' => 'Mật khẩu phải bao gồm: chữ thường, chữ hoa, số và ký tự đặc biệt (@$!%*#?&) để đảm bảo an toàn',
            'phone.required' => 'Số điện thoại là thông tin bắt buộc',
            'phone.string' => 'Số điện thoại chỉ được chứa các ký tự hợp lệ',
            'phone.max' => 'Số điện thoại quá dài, vui lòng nhập tối đa 15 ký tự',
            'phone.unique' => 'Số điện thoại này đã được đăng ký, vui lòng sử dụng số khác',
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
                    errors: $validator->errors()
                ),
                422
            )
        );
    }
}
