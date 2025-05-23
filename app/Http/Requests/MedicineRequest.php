<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedicineRequest extends FormRequest
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
            'category_id' => 'required|string|exists:categories,_id',
            'supplier_id' => 'nullable|string|exists:suppliers,_id',
            'name' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:3|max:1000',
            'variants.limit_quantity' => 'required|integer|min:0',
            'variants.stock_status' => 'required|string|in:IN-STOCK,OUT-OF-STOCK,PRE-ORDER',
            'variants.original_price' => 'required|numeric|min:0',
            'variants.discount_percent' => 'required|numeric|min:0|max:100',
            'variants.is_featured' => 'required|boolean',
            'variants.is_active' => 'required|boolean',
            'details.ingredients' => 'required|string|min:3|max:1000',
            'details.usage' => 'required|array',
            'details.usage.*' => 'required|string|min:2|max:255',
            'details.paramaters.origin' => 'required|string|min:3|max:50',
            'details.paramaters.packaging' => 'required|string|min:3|max:100',
            'usageguide.dosage.adult' => 'required|string|min:3|max:100',
            'usageguide.dosage.child' => 'required|string|min:3|max:100',
            'usageguide.directions' => 'required|array',
            'usageguide.directions.*' => 'required|string|min:2|max:255',
            'usageguide.precautions' => 'required|array',
            'usageguide.precautions.*' => 'required|string|min:2|max:255',
        ];
    }
}
