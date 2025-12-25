<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderdetailRequest extends FormRequest
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
            'OrderId'=>'required|exists:orders,OrderId',
            'TicketId'=>'required|exists:tickets,TicketId',
            'ItemId'=>'nullable|exists:foodanddrinks,ItemId',
            'Quantity'=>'required|integer|min:1',
            'Price'=>'required|numeric|min:0',
            'Status'=>'required|in:Active,Inactive',
            
        ];
    }
}
