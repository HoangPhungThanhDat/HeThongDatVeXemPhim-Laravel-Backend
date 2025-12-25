<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
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
            'Title'       => 'required|string|max:255',
            'Description' => 'nullable|string',
         'GenreId'     => 'required|integer|exists:genres,GenreId',
         
            'Duration'   => 'required|integer|min:1',
            'ReleaseDate'=> 'required|date',
             'PosterUrl'  => $isUpdate 
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'TrailerUrl' => 'nullable|string|max:500',
            'Language'   => 'required|string|max:100',
            'Rated'      => 'nullable|string|max:10',
            'Status'     => 'nullable|string|in:NowShowing,Ended,ComingSoon',
        ];
    }
}
