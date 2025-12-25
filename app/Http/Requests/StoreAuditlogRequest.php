<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuditlogRequest extends FormRequest
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
            
            'UserId' => 'required|exists:users,UserId',
            'Action' => 'required|string|max:255',
            'Description'=> 'nullable|string',
            'IpAddress'=> 'required|string|max:255',
            'DeviceInfo' => 'nullable|string|max:255', 
        ];
    }
}