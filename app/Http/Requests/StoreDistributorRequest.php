<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDistributorRequest extends FormRequest
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
            'Name'     => 'required|string|max:255',
            'MovieId' => 'required|integer|exists:movies,MovieId',
            'Country'  => 'required|string|max:255',
            'Email'   => 'required|email|max:30',
            'Phone'  => 'required|string|max:11',
            'Website' => 'nullable|string|max:255',
            'Status'    => 'required|string|max:20',
        ];
    }
}
