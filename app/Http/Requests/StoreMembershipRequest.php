<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
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
            'UserId'=> 'required|exists:users,UserId',
            'Level'=> 'required|string|max:255',
            'Points'=> 'required|integer|min:0',
            'StartDate'=> 'required|date',
            'EndDate'=> 'nullable|date|after_or_equal:StartDate',
            'Benefits'=> 'nullable|string',
            'Status' => 'required|in:Active,Inactive,Banned',
          
        ];
    }
}
