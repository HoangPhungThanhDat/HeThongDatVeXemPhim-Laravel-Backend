<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsRequest extends FormRequest
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
            'UserId'     => 'required|exists:users,UserId',
            'Title'      => 'required|string|max:255',
            'Slug' => 'nullable|string|unique:news,Slug',
            'Content'    => 'required|string',
            'ImageUrl'   => 'nullable|string|max:255',
            'Status'    => 'required|string|max:20',
        ];
    }
}
