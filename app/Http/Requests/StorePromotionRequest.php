<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [
           'Title' => 'required|string|max:255',
           'Code' => 'required|string|max:100',
           'Description' => 'nullable|string',
          'ImageUrl'  => $isUpdate 
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
           'DiscountType' => 'required|in:Percentage,FixedAmount',
           'DiscountValue' => 'required|numeric|min:0',
           'StartDate' => 'required|date',
           'EndDate' => 'required|date|after_or_equal:StartDate',
           'IsActive' => 'required|boolean',
           'Status' => 'required|in:Active,Inactive',
        ];
    }
}
