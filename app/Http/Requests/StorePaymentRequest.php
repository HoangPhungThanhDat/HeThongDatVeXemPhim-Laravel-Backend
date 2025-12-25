<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'TicketId'       => 'required|integer|exists:tickets,TicketId',
            'Amount'         => 'required|numeric|min:0',
            'PaymentMethod'  => 'required|string|max:100', 
            'PaymentStatus'  => 'required|string|in:pending,completed,failed,cancelled',
            'Status'         => 'required|string|in:active,inactive',
        ];
    }
}
