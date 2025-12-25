<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'UserId'       => 'required|exists:users,UserId',
            'MovieId'     => 'required|exists:movies,MovieId',
            'Rating'      => 'required|integer|min:1|max:5',
            'Comment'     => 'nullable|string|max:1000',
            'Status'      => 'required|in:Visible,Hidden,Deleted',
            
        ];
    }
}
